<?php

namespace HardImpact\OpenCode\Data;

use Spatie\LaravelData\Data;

class Model extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $attachment,
        public bool $reasoning,
        public bool $toolCall,
        public array $cost,
        public array $limit,
        public ?bool $temperature = null,
        public ?string $releaseDate = null,
        public ?string $status = null,
    ) {}
}
