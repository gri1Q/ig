<?php

namespace App\Enums;

enum OperationStatus: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case SKIPPED = 'skipped';
}
