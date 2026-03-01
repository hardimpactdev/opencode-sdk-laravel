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
        $infoData = $data['info'] ?? null;

        if (! is_array($infoData)) {
            throw new \InvalidArgumentException('Message info must be an array, got '.gettype($infoData));
        }

        $role = $infoData['role'] ?? '';
        if ($role !== '' && \HardImpact\OpenCode\Enums\MessageRole::tryFrom($role) === null) {
            $infoData['role'] = 'unknown';
        }

        $info = $role === 'assistant'
            ? AssistantMessage::from($infoData)
            : UserMessage::from($infoData);

        $parts = array_filter(
            array_map(Part::fromTolerant(...), $data['parts'] ?? []),
            fn (?Part $part) => $part !== null,
        );

        return new self(
            info: $info,
            parts: array_values($parts),
        );
    }
}
