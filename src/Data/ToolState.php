<?php

namespace HardImpact\OpenCode\Data;

use HardImpact\OpenCode\Enums\ToolStatus;
use Spatie\LaravelData\Data;

class ToolState extends Data
{
    public function __construct(
        public ToolStatus $status,
        public ?array $input = null,
        public ?string $output = null,
        public ?string $error = null,
        public ?string $title = null,
        public ?array $metadata = null,
        public ?array $time = null,
        public ?array $attachments = null,
    ) {}
}
