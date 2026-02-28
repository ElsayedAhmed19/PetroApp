<?php

namespace App\Dtos;

readonly class TransferFilterDto
{
    public function __construct(
        public ?string $status = null,
    ) {}

    public static function fromArray(array $filters): self
    {
        return new self(
            status: $filters['status'] ?? null
        );
    }

    public function isAll(): bool
    {
        return $this->status === null;
    }
}
