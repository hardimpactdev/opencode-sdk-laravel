<?php

namespace HardImpact\OpenCode\Data;

use Spatie\LaravelData\Data;

class Question extends Data
{
    public function __construct(
        public string $id,
        public string $sessionID,
        public string $title,
        public array $time,
        public mixed $metadata = null,
        public ?string $type = null,
        public ?string $messageID = null,
        public ?string $callID = null,
        public ?string $pattern = null,
    ) {}
}
