<?php

namespace HardImpact\OpenCode\Data;

use HardImpact\OpenCode\Enums\EventType;
use Spatie\LaravelData\Data;

class Event extends Data
{
    public function __construct(
        public EventType $type,
        public array $properties,
    ) {}
}
