<?php

use HardImpact\OpenCode\Resources\SessionResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

test('createAndPrompt creates session at correct workspace path', function (): void {
    $workspacePath = '/home/user/projects/my-app';

    $mockClient = new MockClient([
        // First response: CreateSession
        MockResponse::make([
            'id' => 'ses_new123',
            'title' => 'Test Session',
            'directory' => $workspacePath,
            'time' => ['created' => 1700000000, 'updated' => 1700000000],
        ]),
        // Second response: SendMessage
        MockResponse::make([
            'info' => [
                'id' => 'msg_1',
                'role' => 'assistant',
                'sessionID' => 'ses_new123',
                'modelID' => 'test-model',
                'providerID' => 'test-provider',
                'mode' => 'default',
                'cost' => 0.0,
                'path' => ['cwd' => $workspacePath, 'root' => $workspacePath],
                'time' => ['created' => 1700000000],
                'tokens' => ['input' => 10, 'output' => 20, 'reasoning' => 0, 'cache' => ['read' => 0, 'write' => 0]],
            ],
            'parts' => [
                [
                    'id' => 'prt_1',
                    'type' => 'text',
                    'text' => 'Hello from the assistant',
                    'messageID' => 'msg_1',
                    'sessionID' => 'ses_new123',
                ],
            ],
        ]),
    ]);

    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);

    $resource = new SessionResource($connector);
    $result = $resource->createAndPrompt(
        directory: $workspacePath,
        providerID: 'test-provider',
        modelID: 'test-model',
        text: 'Hello assistant',
        title: 'Test Session',
    );

    expect($result['session'])->toBeInstanceOf(\HardImpact\OpenCode\Data\Session::class);
    expect($result['session']->id)->toBe('ses_new123');
    expect($result['session']->directory)->toBe($workspacePath);

    expect($result['message'])->toBeInstanceOf(\HardImpact\OpenCode\Data\MessageWithParts::class);
    expect($result['message']->parts[0]->text)->toBe('Hello from the assistant');
});

test('createAndPrompt includes workspace path in task meta', function (): void {
    $workspacePath = '/var/www/project';

    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'ses_abc456',
            'title' => 'Task Session',
            'directory' => $workspacePath,
            'projectID' => 'proj_123',
            'time' => ['created' => 1700000000, 'updated' => 1700000000],
        ]),
        MockResponse::make([
            'info' => [
                'id' => 'msg_task1',
                'role' => 'assistant',
                'sessionID' => 'ses_abc456',
                'modelID' => 'claude-3',
                'providerID' => 'anthropic',
                'mode' => 'default',
                'cost' => 0.001,
                'path' => ['cwd' => $workspacePath, 'root' => $workspacePath],
                'time' => ['created' => 1700000000],
                'tokens' => ['input' => 100, 'output' => 50, 'reasoning' => 0, 'cache' => ['read' => 0, 'write' => 0]],
            ],
            'parts' => [
                [
                    'id' => 'prt_task1',
                    'type' => 'text',
                    'text' => 'Task processing complete',
                    'messageID' => 'msg_task1',
                    'sessionID' => 'ses_abc456',
                ],
            ],
        ]),
    ]);

    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);

    $resource = new SessionResource($connector);
    $result = $resource->createAndPrompt(
        directory: $workspacePath,
        providerID: 'anthropic',
        modelID: 'claude-3',
        text: 'Process this task',
        title: 'Task Session',
    );

    expect($result['session']->directory)->toBe($workspacePath);
    expect($result['session']->projectID)->toBe('proj_123');

    expect($result['message']->info->path['cwd'])->toBe($workspacePath);
    expect($result['message']->info->path['root'])->toBe($workspacePath);
});
