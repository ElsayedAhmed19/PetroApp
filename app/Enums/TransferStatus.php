<?php

namespace App\Enums;

enum TransferStatus: string
{
    case APPROVED = 'approved';
    case PENDING = 'pending';
    case REJECTED = 'rejected';
}
