<?php

use HardImpact\OpenCode\Enums\EventType;
use HardImpact\OpenCode\Support\EventStream;
use Psr\Http\Message\StreamInterface;

function createStreamFromString(string $content): StreamInterface
{
    $stream = new class($content) implements StreamInterface {
        private int $position = 0;

        public function __construct(private string $content) {}

        public function __toString(): string { return $this->content; }

        public function close(): void {}

        public function detach() { return null; }

        public function getSize(): ?int { return strlen($this->content); }

        public function tell(): int { return $this->position; }

        public function eof(): bool { return $this->position >= strlen($this->content); }

        public function isSeekable(): bool { return false; }

        public function seek(int $offset, int $whence = SEEK_SET): void {}

        public function rewind(): void { $this->position = 0; }

        public function isWritable(): bool { return false; }

        public function write(string $string): int { return 0; }

        public function isReadable(): bool { return true; }

        public function read(int $length): string
        {
            $data = substr($this->content, $this->position, $length);
            $this->position += strlen($data);

            return $data;
        }

        public function getContents(): string
        {
            return substr($this->content, $this->position);
        }

        public function getMetadata(?string $key = null) { return null; }
    };

    return $stream;
}

it('parses SSE events from stream', function () {
    $sseData = "data: {\"type\":\"session.idle\",\"properties\":{\"sessionID\":\"ses_123\"}}\n\ndata: {\"type\":\"message.part.updated\",\"properties\":{\"part\":{\"id\":\"p1\"}}}\n\n";

    $stream = createStreamFromString($sseData);
    $eventStream = new EventStream($stream);

    $events = iterator_to_array($eventStream->events());

    expect($events)->toHaveCount(2);
    expect($events[0]->type)->toBe(EventType::SessionIdle);
    expect($events[0]->properties)->toBe(['sessionID' => 'ses_123']);
    expect($events[1]->type)->toBe(EventType::MessagePartUpdated);
});

it('parses SSE events with event type line', function () {
    $sseData = "event: session.updated\ndata: {\"properties\":{\"info\":{\"id\":\"ses_123\"}}}\n\n";

    $stream = createStreamFromString($sseData);
    $eventStream = new EventStream($stream);

    $events = iterator_to_array($eventStream->events());

    expect($events)->toHaveCount(1);
    expect($events[0]->type)->toBe(EventType::SessionUpdated);
});

it('skips unknown event types', function () {
    $sseData = "data: {\"type\":\"unknown.event\",\"properties\":{}}\n\ndata: {\"type\":\"session.idle\",\"properties\":{}}\n\n";

    $stream = createStreamFromString($sseData);
    $eventStream = new EventStream($stream);

    $events = iterator_to_array($eventStream->events());

    expect($events)->toHaveCount(1);
    expect($events[0]->type)->toBe(EventType::SessionIdle);
});

it('handles empty stream', function () {
    $stream = createStreamFromString('');
    $eventStream = new EventStream($stream);

    $events = iterator_to_array($eventStream->events());

    expect($events)->toHaveCount(0);
});
