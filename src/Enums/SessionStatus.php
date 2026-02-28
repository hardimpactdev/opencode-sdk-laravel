<?php

declare(strict_types=1);

namespace HardImpact\OpenCode\Enums;

enum SessionStatus: string
{
    case Created = 'created';
    case Active = 'active';
    case Interrupted = 'interrupted';
    case Recovered = 'recovered';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Active => 'Active',
            self::Interrupted => 'Interrupted',
            self::Recovered => 'Recovered',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }
}
