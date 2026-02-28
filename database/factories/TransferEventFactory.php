<?php

namespace Database\Factories;

use App\Models\TransferEvent;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransferEvent>
 */
class TransferEventFactory extends Factory
{
    protected $model = TransferEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => $this->faker->uuid(),
            'station_id' => Station::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement(['approved', 'pending', 'rejected']),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
