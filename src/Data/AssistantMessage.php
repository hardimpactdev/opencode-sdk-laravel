<?php

namespace HardImpact\OpenCode\Data;

use HardImpact\OpenCode\Enums\MessageRole;
use Spatie\LaravelData\Data;

class AssistantMessage extends Data
{
    public function __construct(
        public string $id,
        public MessageRole $role,
        public string $sessionID,
        public string $modelID,
        public string $providerID,
        public string $mode,
        public float $cost,
        public array $path,
        public array $system,
        public array $time,
        public array $tokens,
        public ?array $error = null,
        public ?bool $summary = null,
        public ?string $parentID = null,
    ) {}
}
