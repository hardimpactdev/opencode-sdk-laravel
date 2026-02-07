<?php

namespace HardImpact\OpenCode\Data;

use Spatie\LaravelData\Data;

class Session extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public string $version,
        public array $time,
        public ?string $parentID = null,
        public ?array $revert = null,
        public ?array $share = null,
        public ?string $directory = null,
        public ?string $projectID = null,
        public ?array $summary = null,
    ) {}
}
