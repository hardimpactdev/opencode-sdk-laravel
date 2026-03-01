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

    public function shouldComplete(): bool
    {
        return in_array($this->state, [SessionState::Completed, SessionState::Idle], true);
    }

    public function isMissing(): bool
    {
        return $this->state === SessionState::Missing;
    }
}
