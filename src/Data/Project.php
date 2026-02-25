<?php

namespace HardImpact\OpenCode\Data;

use Spatie\LaravelData\Data;

class Project extends Data
{
    public function __construct(
        public string $id,
        public string $worktree,
        public array $time,
        public array $sandboxes = [],
        public ?string $vcs = null,
        public ?string $name = null,
        public ?array $icon = null,
        public ?array $commands = null,
    ) {}
}
