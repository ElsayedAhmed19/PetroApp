<?php

namespace App\Dtos;

use App\Enums\TransferStatus;

readonly class TransferFilterDto
{
    public function __construct(
        public ?TransferStatus $status = null,
    ) {}

    public static function fromArray(array $filters): self
    {
        return new self(
            status: isset($filters['status'])
                ? TransferStatus::tryFrom($filters['status'])
                : null
        );
    }

    public function isAll(): bool
    {
        return $this->status === null;
    }
}
