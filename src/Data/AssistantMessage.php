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
        public array $time,
        public ?string $modelID = null,
        public ?string $providerID = null,
        public ?string $mode = null,
        public ?float $cost = null,
        public ?array $path = null,
        public ?array $tokens = null,
        public ?array $system = null,
        public ?array $error = null,
        public ?bool $summary = null,
        public ?string $parentID = null,
    ) {}
}
