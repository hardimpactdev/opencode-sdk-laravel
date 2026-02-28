<?php

declare(strict_types=1);

namespace HardImpact\OpenCode\Enums;

enum SessionState: string
{
    case Active = 'active';
    case Idle = 'idle';
    case Completed = 'completed';
    case Missing = 'missing';
}
