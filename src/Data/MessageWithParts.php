<?php

namespace HardImpact\OpenCode\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class MessageWithParts extends Data
{
    public function __construct(
        public UserMessage|AssistantMessage $info,
        #[DataCollectionOf(Part::class)]
        public array $parts,
    ) {}

    public static function fromResponse(array $data): self
    {
        $info = ($data['info']['role'] ?? '') === 'assistant'
            ? AssistantMessage::from($data['info'])
            : UserMessage::from($data['info']);

        return new self(
            info: $info,
            parts: array_map(Part::from(...), $data['parts'] ?? []),
        );
    }
}
