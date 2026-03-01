<?php

namespace HardImpact\OpenCode\Support;

use Generator;
use HardImpact\OpenCode\Data\Event;
use HardImpact\OpenCode\Enums\EventType;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class EventStream
{
    private const int MAX_BUFFER_SIZE = 1_048_576; // 1MB

    protected string $buffer = '';

    public function __construct(
        protected StreamInterface $stream,
    ) {}

    /** @return Generator<Event> */
    public function events(): Generator
    {
        while (! $this->stream->eof()) {
            $chunk = $this->stream->read(8192);

            if ($chunk === '') {
                usleep(10_000); // 10ms backoff to prevent CPU spin on empty reads

                continue;
            }

            // Normalize line endings: \r\n and \r to \n
            $this->buffer .= str_replace(["\r\n", "\r"], "\n", $chunk);

            while (($pos = strpos($this->buffer, "\n\n")) !== false) {
                $rawEvent = substr($this->buffer, 0, $pos);
                $this->buffer = substr($this->buffer, $pos + 2);

                $event = $this->parseEvent($rawEvent);
                if ($event !== null) {
                    yield $event;
                }
            }

            if (strlen($this->buffer) > self::MAX_BUFFER_SIZE) {
                throw new RuntimeException('SSE buffer exceeded maximum size');
            }
        }

        if (trim($this->buffer) !== '') {
            $event = $this->parseEvent($this->buffer);
            if ($event !== null) {
                yield $event;
            }
        }
    }

    protected function parseEvent(string $raw): ?Event
    {
        /** @var list<string> $dataLines */
        $dataLines = [];
        $eventType = null;

        foreach (explode("\n", $raw) as $line) {
            if (str_starts_with($line, 'data:')) {
                $dataLines[] = substr($line, 5) === '' ? '' : ltrim(substr($line, 5), ' ');
            } elseif (str_starts_with($line, 'event:')) {
                $eventType = trim(substr($line, 6));
            }
        }

        if ($dataLines === []) {
            return null;
        }

        $data = implode("\n", $dataLines);
        $decoded = json_decode($data, true);
        if (! is_array($decoded)) {
            return null;
        }

        $type = $eventType ?? ($decoded['type'] ?? null);
        if ($type === null) {
            return null;
        }

        $enumType = EventType::tryFrom($type);
        if ($enumType === null) {
            return null;
        }

        return new Event(
            type: $enumType,
            properties: $decoded['properties'] ?? $decoded,
        );
    }
}
