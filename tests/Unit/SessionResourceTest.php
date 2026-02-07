<?php

use HardImpact\OpenCode\Data\MessageWithParts;
use HardImpact\OpenCode\Data\Part;
use HardImpact\OpenCode\Data\Session;
use HardImpact\OpenCode\Enums\PartType;
use HardImpact\OpenCode\Resources\SessionResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mockClient = new MockClient([
        MockResponse::make(['id' => 'ses_123', 'title' => 'Test']),
    ]);
});

test('isIdle returns true for sessions older than threshold', function () {
    $oldTimestamp = (int) (microtime(true) * 1000) - 150_000; // 150 seconds ago
    
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'ses_123',
            'title' => 'Test',
            'time' => ['updated' => $oldTimestamp],
        ]),
    ]);
    
    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);
    
    $resource = new SessionResource($connector);
    $result = $resource->isIdle('ses_123', null, 120_000);
    
    expect($result)->toBeTrue();
});

test('isIdle returns false for recent sessions', function () {
    $recentTimestamp = (int) (microtime(true) * 1000) - 30_000; // 30 seconds ago
    
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'ses_123',
            'title' => 'Test',
            'time' => ['updated' => $recentTimestamp],
        ]),
    ]);
    
    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);
    
    $resource = new SessionResource($connector);
    $result = $resource->isIdle('ses_123', null, 120_000);
    
    expect($result)->toBeFalse();
});

test('isActive returns opposite of isIdle', function () {
    $recentTimestamp = (int) (microtime(true) * 1000) - 30_000;
    
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'ses_123',
            'title' => 'Test',
            'time' => ['updated' => $recentTimestamp],
        ]),
    ]);
    
    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);
    
    $resource = new SessionResource($connector);
    
    expect($resource->isActive('ses_123'))->toBeTrue();
    expect($resource->isIdle('ses_123'))->toBeFalse();
});

test('getLastMessageText extracts text from message parts', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            [
                'id' => 'msg_1',
                'parts' => [
                    [
                        'id' => 'prt_1',
                        'type' => 'text',
                        'text' => 'Task completed successfully.',
                        'messageID' => 'msg_1',
                        'sessionID' => 'ses_123',
                    ],
                ],
            ],
        ]),
    ]);
    
    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);
    
    $resource = new SessionResource($connector);
    $text = $resource->getLastMessageText('ses_123');
    
    expect($text)->toBe('Task completed successfully.');
});

test('hasCompletionIndicators detects completion patterns', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            [
                'id' => 'msg_1',
                'parts' => [
                    [
                        'id' => 'prt_1',
                        'type' => 'text',
                        'text' => 'Task #5 completed successfully. Summary: All tests pass.',
                        'messageID' => 'msg_1',
                        'sessionID' => 'ses_123',
                    ],
                ],
            ],
        ]),
    ]);
    
    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);
    
    $resource = new SessionResource($connector);
    $result = $resource->hasCompletionIndicators('ses_123', ['/completed successfully/i']);
    
    expect($result)->toBeTrue();
});

test('hasCompletionIndicators returns false when no patterns match', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            [
                'id' => 'msg_1',
                'parts' => [
                    [
                        'id' => 'prt_1',
                        'type' => 'text',
                        'text' => 'Still working on the task...',
                        'messageID' => 'msg_1',
                        'sessionID' => 'ses_123',
                    ],
                ],
            ],
        ]),
    ]);
    
    $connector = new \HardImpact\OpenCode\OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);
    
    $resource = new SessionResource($connector);
    $result = $resource->hasCompletionIndicators('ses_123', ['/completed successfully/i']);
    
    expect($result)->toBeFalse();
});
