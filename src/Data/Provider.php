<?php

namespace HardImpact\OpenCode\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class Provider extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public array $env,
        #[DataCollectionOf(Model::class)]
        public array $models,
        public ?string $api = null,
        public ?string $npm = null,
    ) {}
}
