<?php

namespace App\Dtos;

use App\Enums\TransferStatus;
use DateTimeImmutable;
use Illuminate\Support\Collection;

readonly class TransferEventDto
{
    public function __construct(
        public string $eventId,
        public int $stationId,
        public float $amount,
        public TransferStatus $status,
        public DateTimeImmutable $createdAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['event_id'],
            stationId: (int) $data['station_id'],
            amount: (float) $data['amount'],
            status: $data['status'] instanceof TransferStatus
                ? $data['status']
                : TransferStatus::from($data['status']),
            createdAt: new DateTimeImmutable($data['created_at'])
        );
    }

    public static function collection(array $data): Collection
    {
        $collection = collect();

        foreach ($data as $event) {
            $collection->push(self::fromArray($event));
        }

        return $collection;
    }
}
