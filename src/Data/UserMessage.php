<?php

namespace HardImpact\OpenCode\Data;

use HardImpact\OpenCode\Enums\MessageRole;
use Spatie\LaravelData\Data;

class UserMessage extends Data
{
    public function __construct(
        public string $id,
        public MessageRole $role,
        public string $sessionID,
        public array $time,
    ) {}
}
