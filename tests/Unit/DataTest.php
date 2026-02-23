<?php

use HardImpact\OpenCode\Data\AssistantMessage;
use HardImpact\OpenCode\Data\Event;
use HardImpact\OpenCode\Data\MessageWithParts;
use HardImpact\OpenCode\Data\Part;
use HardImpact\OpenCode\Data\Question;
use HardImpact\OpenCode\Data\Session;
use HardImpact\OpenCode\Data\UserMessage;
use HardImpact\OpenCode\Enums\EventType;
use HardImpact\OpenCode\Enums\MessageRole;
use HardImpact\OpenCode\Enums\PartType;

it('creates a Session from array', function (): void {
    $session = Session::from([
        'id' => 'ses_123',
        'title' => 'Test Session',
        'version' => '1.0.0',
        'time' => ['created' => 1700000000, 'updated' => 1700000001],
    ]);

    expect($session->id)->toBe('ses_123');
    expect($session->title)->toBe('Test Session');
    expect($session->version)->toBe('1.0.0');
    expect($session->time)->toBe(['created' => 1700000000, 'updated' => 1700000001]);
    expect($session->parentID)->toBeNull();
});

it('creates a UserMessage from array', function (): void {
    $message = UserMessage::from([
        'id' => 'msg_001',
        'role' => 'user',
        'sessionID' => 'ses_123',
        'time' => ['created' => 1700000000],
    ]);

    expect($message->id)->toBe('msg_001');
    expect($message->role)->toBe(MessageRole::User);
    expect($message->sessionID)->toBe('ses_123');
});

it('creates an AssistantMessage from array', function (): void {
    $message = AssistantMessage::from([
        'id' => 'msg_002',
        'role' => 'assistant',
        'sessionID' => 'ses_123',
        'modelID' => 'claude-sonnet-4-20250514',
        'providerID' => 'anthropic',
        'mode' => 'default',
        'cost' => 0.003,
        'path' => ['cwd' => '/tmp', 'root' => '/tmp'],
        'system' => ['You are helpful.'],
        'time' => ['created' => 1700000000, 'completed' => 1700000005],
        'tokens' => [
            'input' => 100,
            'output' => 50,
            'reasoning' => 0,
            'cache' => ['read' => 0, 'write' => 0],
        ],
    ]);

    expect($message->id)->toBe('msg_002');
    expect($message->role)->toBe(MessageRole::Assistant);
    expect($message->modelID)->toBe('claude-sonnet-4-20250514');
    expect($message->cost)->toBe(0.003);
    expect($message->error)->toBeNull();
});

it('creates a Part from array', function (): void {
    $part = Part::from([
        'id' => 'part_001',
        'type' => 'text',
        'messageID' => 'msg_002',
        'sessionID' => 'ses_123',
        'text' => 'Hello world',
    ]);

    expect($part->id)->toBe('part_001');
    expect($part->type)->toBe(PartType::Text);
    expect($part->text)->toBe('Hello world');
});

it('creates a tool Part from array', function (): void {
    $part = Part::from([
        'id' => 'part_002',
        'type' => 'tool',
        'messageID' => 'msg_002',
        'sessionID' => 'ses_123',
        'tool' => 'bash',
        'callID' => 'call_001',
        'state' => ['status' => 'completed', 'input' => ['command' => 'ls'], 'output' => 'file.txt', 'title' => 'List files', 'metadata' => [], 'time' => ['start' => 1700000000, 'end' => 1700000001]],
    ]);

    expect($part->type)->toBe(PartType::Tool);
    expect($part->tool)->toBe('bash');
    expect($part->state['status'])->toBe('completed');
});

it('creates an Event from array', function (): void {
    $event = Event::from([
        'type' => 'session.idle',
        'properties' => ['sessionID' => 'ses_123'],
    ]);

    expect($event->type)->toBe(EventType::SessionIdle);
    expect($event->properties)->toBe(['sessionID' => 'ses_123']);
});

it('creates a Question from array', function (): void {
    $question = Question::from([
        'id' => 'perm_001',
        'sessionID' => 'ses_123',
        'title' => 'Allow bash command: ls',
        'time' => ['created' => 1700000000],
        'type' => 'bash',
    ]);

    expect($question->id)->toBe('perm_001');
    expect($question->title)->toBe('Allow bash command: ls');
    expect($question->type)->toBe('bash');
});

it('creates a MessageWithParts from response data', function (): void {
    $data = [
        'info' => [
            'id' => 'msg_002',
            'role' => 'assistant',
            'sessionID' => 'ses_123',
            'modelID' => 'claude-sonnet-4-20250514',
            'providerID' => 'anthropic',
            'mode' => 'default',
            'cost' => 0.003,
            'path' => ['cwd' => '/tmp', 'root' => '/tmp'],
            'system' => [],
            'time' => ['created' => 1700000000],
            'tokens' => ['input' => 100, 'output' => 50, 'reasoning' => 0, 'cache' => ['read' => 0, 'write' => 0]],
        ],
        'parts' => [
            [
                'id' => 'part_001',
                'type' => 'text',
                'messageID' => 'msg_002',
                'sessionID' => 'ses_123',
                'text' => 'Hello!',
            ],
        ],
    ];

    $messageWithParts = MessageWithParts::fromResponse($data);

    expect($messageWithParts->info)->toBeInstanceOf(AssistantMessage::class);
    expect($messageWithParts->parts)->toHaveCount(1);
    expect($messageWithParts->parts[0])->toBeInstanceOf(Part::class);
    expect($messageWithParts->parts[0]->text)->toBe('Hello!');
});
