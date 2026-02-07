<?php

namespace HardImpact\OpenCode\Data;

use HardImpact\OpenCode\Enums\PartType;
use Spatie\LaravelData\Data;

class Part extends Data
{
    public function __construct(
        public string $id,
        public PartType $type,
        public string $messageID,
        public string $sessionID,
        public ?string $text = null,
        public ?string $mime = null,
        public ?string $url = null,
        public ?string $filename = null,
        public ?string $tool = null,
        public ?string $callID = null,
        public ?array $state = null,
        public ?string $snapshot = null,
        public ?array $files = null,
        public ?string $hash = null,
        public ?float $cost = null,
        public ?array $tokens = null,
        public ?string $reason = null,
        public ?bool $synthetic = null,
        public ?array $time = null,
        public ?array $metadata = null,
        public ?array $source = null,
    ) {}
}
