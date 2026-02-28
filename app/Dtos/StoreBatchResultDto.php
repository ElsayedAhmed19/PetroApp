<?php

namespace App\Dtos;

readonly class StoreBatchResultDto
{
    public function __construct(
        public int $inserted,
        public int $duplicates,
        public array $validation_failed_items = [],
    ) {}
}
