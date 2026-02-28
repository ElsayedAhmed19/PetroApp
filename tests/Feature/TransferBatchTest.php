<?php

namespace Tests\Feature;

use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransferBatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement: Batch insert returns correct inserted/duplicates.
     */
    public function test_batch_insert_returns_correct_inserted_duplicates_counts()
    {
        $station = Station::factory()->create();
        $eventId1 = (string) Str::uuid();
        $eventId2 = (string) Str::uuid();

        // 1. Initial Insert
        $response = $this->postJson('/api/transfers', [
            'events' => [
                [
                    'event_id' => $eventId1,
                    'station_id' => $station->id,
                    'amount' => 100.50,
                    'status' => 'approved',
                    'created_at' => '2026-02-28T10:00:00Z'
                ],
                [
                    'event_id' => $eventId2,
                    'station_id' => $station->id,
                    'amount' => 200.75,
                    'status' => 'pending',
                    'created_at' => '2026-02-28T11:00:00Z'
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('inserted', 2)
            ->assertJsonPath('duplicates', 0);

        // 2. Duplicate Insert
        $response = $this->postJson('/api/transfers', [
            'events' => [
                [
                    'event_id' => $eventId1, // Duplicate
                    'station_id' => $station->id,
                    'amount' => 100.50,
                    'status' => 'approved',
                    'created_at' => '2026-02-28T10:00:00Z'
                ],
                [
                    'event_id' => (string) Str::uuid(), // New
                    'station_id' => $station->id,
                    'amount' => 300.00,
                    'status' => 'approved',
                    'created_at' => '2026-02-28T12:00:00Z'
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('duplicates', 1);
    }

    /**
     * Requirement: Duplicate event doesn’t change totals.
     */
    public function test_duplicate_event_does_not_change_totals()
    {
        $station = Station::factory()->create();
        $eventId = (string) Str::uuid();

        $eventData = [
            'event_id' => $eventId,
            'station_id' => $station->id,
            'amount' => 500.00,
            'status' => 'approved',
            'created_at' => '2026-02-28T10:00:00Z'
        ];

        // First upload
        $this->postJson('/api/transfers', ['events' => [$eventData]]);

        // Second upload (re-try)
        $this->postJson('/api/transfers', ['events' => [$eventData]]);

        $summaryResponse = $this->getJson("/api/stations/{$station->id}/summary");

        // Total should stay 500, not become 1000
        $summaryResponse->assertStatus(200)
            ->assertJson(['total_approved_amount' => 500]);
    }

    /**
     * Requirement: Out-of-order arrival still produces same totals.
     */
    public function test_out_of_order_arrival_produces_same_totals()
    {
        $station = Station::factory()->create();
        $id1 = (string) Str::uuid();
        $id2 = (string) Str::uuid();

        $data1 = ['event_id' => $id1, 'station_id' => $station->id, 'amount' => 100, 'status' => 'approved', 'created_at' => '2026-02-28T10:00:00Z'];
        $data2 = ['event_id' => $id2, 'station_id' => $station->id, 'amount' => 200, 'status' => 'approved', 'created_at' => '2026-02-28T09:00:00Z']; // Earlier time, arrived later

        $this->postJson('/api/transfers', ['events' => [$data1]]);
        $this->postJson('/api/transfers', ['events' => [$data2]]);

        $summary = $this->getJson("/api/stations/{$station->id}/summary");
        $summary->assertJson(['total_approved_amount' => 300]);
    }

    /**
     * Requirement: Summary endpoint correctness per station.
     */
    public function test_summary_is_correct_per_station()
    {
        $stationA = Station::factory()->create();
        $stationB = Station::factory()->create();

        // Ingest for Station A
        $this->postJson('/api/transfers', ['events' => [
            ['event_id' => Str::uuid(), 'station_id' => $stationA->id, 'amount' => 100, 'status' => 'approved', 'created_at' => now()->toIso8601String()],
            ['event_id' => Str::uuid(), 'station_id' => $stationA->id, 'amount' => 50, 'status' => 'pending', 'created_at' => now()->toIso8601String()],
        ]]);

        // Ingest for Station B
        $this->postJson('/api/transfers', ['events' => [
            ['event_id' => Str::uuid(), 'station_id' => $stationB->id, 'amount' => 300, 'status' => 'approved', 'created_at' => now()->toIso8601String()],
        ]]);

        // Check Station A (Only approved should sum, but all count)
        $this->getJson("/api/stations/{$stationA->id}/summary")
            ->assertJson([
                'total_approved_amount' => 100,
                'events_count' => 2
            ]);

        // Check Station B
        $this->getJson("/api/stations/{$stationB->id}/summary")
            ->assertJson([
                'total_approved_amount' => 300,
                'events_count' => 1
            ]);
    }

    /**
     * Requirement: Validation failure behavior (Partial Accept).
     */
    public function test_partial_accept_on_validation_failure()
    {
        config(['transfers.batch_strategy' => 'partial']);

        $station = Station::factory()->create();

        $response = $this->postJson('/api/transfers', [
            'events' => [
                [
                    'event_id' => (string) Str::uuid(),
                    'station_id' => $station->id,
                    'amount' => 100,
                    'status' => 'approved',
                    'created_at' => '2026-02-28T10:00:00Z'
                ],
                [
                    'event_id' => 'invalid-uuid',
                    'station_id' => $station->id,
                    'amount' => -50,
                    'status' => 'approved',
                    'created_at' => 'not-a-date'
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('validation_failed_items.0.index', 1);
    }

    /**
     * Requirement: Validation failure behavior (Fail Fast).
     */
    public function test_fail_fast_validation_strategy()
    {
        config(['transfers.batch_strategy' => 'fail-fast']);

        $station = Station::factory()->create();

        $response = $this->postJson('/api/transfers', [
            'events' => [
                [
                    'event_id' => (string) Str::uuid(),
                    'station_id' => $station->id,
                    'amount' => 100,
                    'status' => 'approved',
                    'created_at' => '2026-02-28T10:00:00Z'
                ],
                [
                    'event_id' => 'invalid-uuid',
                    'station_id' => $station->id,
                    'amount' => 100,
                    'status' => 'approved',
                    'created_at' => '2026-02-28T10:00:00Z'
                ]
            ]
        ]);

        $response->assertStatus(422);
    }

    /**
     * Additional Case: Verify that total_approved_amount ONLY sums approved events.
     */
    public function test_summary_total_approved_only_includes_approved_status()
    {
        $station = Station::factory()->create();

        $this->postJson('/api/transfers', ['events' => [
            ['event_id' => Str::uuid(), 'station_id' => $station->id, 'amount' => 100, 'status' => 'approved', 'created_at' => now()->toIso8601String()],
            ['event_id' => Str::uuid(), 'station_id' => $station->id, 'amount' => 500, 'status' => 'pending', 'created_at' => now()->toIso8601String()],
            ['event_id' => Str::uuid(), 'station_id' => $station->id, 'amount' => 999, 'status' => 'rejected', 'created_at' => now()->toIso8601String()],
        ]]);

        $this->getJson("/api/stations/{$station->id}/summary")
            ->assertJson(['total_approved_amount' => 100]); // Ignores pending and rejected
    }

    /**
     * Requirement: Concurrent ingestion of same IDs doesn’t double count.
     */
    public function test_concurrent_ingestion_of_same_ids_does_not_double_count()
    {
        $station = Station::factory()->create();
        $eventId = (string) Str::uuid();
        $eventData = [
            'event_id' => $eventId,
            'station_id' => $station->id,
            'amount' => 100,
            'status' => 'approved',
            'created_at' => now()->toIso8601String()
        ];

        // Simulate multiple requests with same event ID
        $this->postJson('/api/transfers', ['events' => [$eventData]]);
        $this->postJson('/api/transfers', ['events' => [$eventData]]);
        $this->postJson('/api/transfers', ['events' => [$eventData]]);

        $this->getJson("/api/stations/{$station->id}/summary")
            ->assertJson(['total_approved_amount' => 100, 'events_count' => 1]);
    }

    /**
     * Additional Case: Transfer amount can be exactly 0.
     */
    public function test_transfer_amount_can_be_zero()
    {
        $station = Station::factory()->create();

        $response = $this->postJson('/api/transfers', [
            'events' => [[
                'event_id' => Str::uuid(),
                'station_id' => $station->id,
                'amount' => 0,
                'status' => 'approved',
                'created_at' => now()->toIso8601String()
            ]]
        ]);

        $response->assertStatus(201)->assertJson(['inserted' => 1]);
    }

    /**
     * Additional Case: API rejects a completely empty events array.
     */
    public function test_rejects_empty_events_array()
    {
        $response = $this->postJson('/api/transfers', ['events' => []]);
        $response->assertStatus(422);
    }

    /**
     * Additional Case: Station must exist in the database.
     */
    public function test_rejects_transfers_for_non_existent_station()
    {
        $response = $this->postJson('/api/transfers', [
            'events' => [[
                'event_id' => Str::uuid(),
                'station_id' => 9999, // Doesn't exist
                'amount' => 100,
                'status' => 'approved',
                'created_at' => now()->toIso8601String()
            ]]
        ]);

        $response->assertStatus(201)
            ->assertJson(['inserted' => 0, 'validation_failed_items' => [['index' => 0]]]);
    }

    /**
     * Additional Case: Rejects malformed JSON body.
     */
    public function test_rejects_malformed_request_body()
    {
        $response = $this->postJson('/api/transfers', ['not_events' => []]);
        $response->assertStatus(422);
    }
}
