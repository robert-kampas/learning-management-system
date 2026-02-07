<?php

declare(strict_types=1);

namespace App\Enum;

enum ProgressLogType: string
{
    case STARTED = 'started';
    case COMPLETED = 'completed';
}
