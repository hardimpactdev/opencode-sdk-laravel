<?php

declare(strict_types=1);

namespace HardImpact\OpenCode\Data;

use HardImpact\OpenCode\Enums\SessionState;

final readonly class SessionAssessment
{
    public function __construct(
        public SessionState $state,
        public ?string $reason = null,
    ) {}

    public function isTerminal(): bool
    {
        return in_array($this->state, [SessionState::Completed, SessionState::Idle, SessionState::Missing], true);
    }
}
