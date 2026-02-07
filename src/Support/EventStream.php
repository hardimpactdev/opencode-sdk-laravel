<?php

namespace HardImpact\OpenCode\Support;

use Generator;
use HardImpact\OpenCode\Data\Event;
use HardImpact\OpenCode\Enums\EventType;
use Psr\Http\Message\StreamInterface;

class EventStream
{
    protected StreamInterface $stream;

    protected string $buffer = '';

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /** @return Generator<Event> */
    public function events(): Generator
    {
        while (! $this->stream->eof()) {
            $this->buffer .= $this->stream->read(8192);

            while (($pos = strpos($this->buffer, "\n\n")) !== false) {
                $rawEvent = substr($this->buffer, 0, $pos);
                $this->buffer = substr($this->buffer, $pos + 2);

                $event = $this->parseEvent($rawEvent);
                if ($event !== null) {
                    yield $event;
                }
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
        $data = null;
        $eventType = null;

        foreach (explode("\n", $raw) as $line) {
            if (str_starts_with($line, 'data:')) {
                $data = trim(substr($line, 5));
            } elseif (str_starts_with($line, 'event:')) {
                $eventType = trim(substr($line, 6));
            }
        }

        if ($data === null) {
            return null;
        }

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
