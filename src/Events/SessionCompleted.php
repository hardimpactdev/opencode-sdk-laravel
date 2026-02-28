<?php

declare(strict_types=1);

namespace HardImpact\OpenCode\Events;

use HardImpact\OpenCode\Models\OpenCodeSession;
use Illuminate\Foundation\Events\Dispatchable;

final class SessionCompleted
{
    use Dispatchable;

    public function __construct(
        public OpenCodeSession $session,
    ) {}
}
