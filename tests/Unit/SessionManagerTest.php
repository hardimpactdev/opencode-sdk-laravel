<?php

use HardImpact\OpenCode\Data\SessionAssessment;
use HardImpact\OpenCode\Enums\SessionState;
use HardImpact\OpenCode\Enums\SessionStatus;
use HardImpact\OpenCode\Events\SessionActivated;
use HardImpact\OpenCode\Events\SessionBecameIdle;
use HardImpact\OpenCode\Events\SessionCompleted;
use HardImpact\OpenCode\Events\SessionFailed;
use HardImpact\OpenCode\Events\SessionInterrupted;
use HardImpact\OpenCode\Events\SessionRecovered;
use HardImpact\OpenCode\Models\OpenCodeSession;
use HardImpact\OpenCode\SessionManager;
use Illuminate\Support\Facades\Event;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->manager = new SessionManager;
});

describe('assess', function (): void {
    test('returns Missing when API session not found', function (): void {
        MockClient::global([
            MockResponse::make(status: 404),
        ]);

        $session = createTestSession();
        $assessment = $this->manager->assess($session);

        expect($assessment)->toBeInstanceOf(SessionAssessment::class);
        expect($assessment->state)->toBe(SessionState::Missing);
        expect($assessment->isTerminal())->toBeTrue();

        MockClient::destroyGlobal();
    });

    test('returns Completed when file changes and stale', function (): void {
        $staleTimestamp = (int) (microtime(true) * 1000) - 150_000; // 150s ago

        MockClient::global([
            MockResponse::make([
                'id' => 'ses_123',
                'title' => 'Test',
                'time' => ['updated' => $staleTimestamp],
                'summary' => ['files' => 3, 'additions' => 50, 'deletions' => 10],
            ]),
        ]);

        $session = createTestSession();
        $assessment = $this->manager->assess($session);

        expect($assessment->state)->toBe(SessionState::Completed);
        expect($assessment->isTerminal())->toBeTrue();
        expect($assessment->reason)->toContain('File changes');

        MockClient::destroyGlobal();
    });

    test('returns Completed when completion indicators match', function (): void {
        $recentTimestamp = (int) (microtime(true) * 1000) - 30_000; // 30s ago

        MockClient::global([
            // get() response
            MockResponse::make([
                'id' => 'ses_123',
                'title' => 'Test',
                'time' => ['updated' => $recentTimestamp],
            ]),
            // messages() response
            MockResponse::make([
                [
                    'info' => [
                        'id' => 'msg_1',
                        'role' => 'assistant',
                        'sessionID' => 'ses_123',
                        'modelID' => 'test',
                        'providerID' => 'test',
                        'mode' => 'default',
                        'cost' => 0.0,
                        'path' => ['cwd' => '/tmp', 'root' => '/tmp'],
                        'time' => ['created' => 1700000000],
                        'tokens' => ['input' => 0, 'output' => 0, 'reasoning' => 0, 'cache' => ['read' => 0, 'write' => 0]],
                    ],
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

        $session = createTestSession();
        $assessment = $this->manager->assess($session, ['/completed successfully/i']);

        expect($assessment->state)->toBe(SessionState::Completed);
        expect($assessment->reason)->toContain('Completion indicator');

        MockClient::destroyGlobal();
    });

    test('returns Idle when session is stale', function (): void {
        $staleTimestamp = (int) (microtime(true) * 1000) - 150_000; // 150s ago

        MockClient::global([
            MockResponse::make([
                'id' => 'ses_123',
                'title' => 'Test',
                'time' => ['updated' => $staleTimestamp],
                // no summary / no file changes
            ]),
        ]);

        $session = createTestSession();
        $assessment = $this->manager->assess($session);

        expect($assessment->state)->toBe(SessionState::Idle);
        expect($assessment->isTerminal())->toBeTrue();

        MockClient::destroyGlobal();
    });

    test('returns Idle when state is null and exceeds fallback threshold', function (): void {
        $staleTimestamp = (int) (microtime(true) * 1000) - 60_000; // 60s ago (under default 120s threshold)

        MockClient::global([
            MockResponse::make([
                'id' => 'ses_123',
                'title' => 'Test',
                'time' => ['updated' => $staleTimestamp],
                'state' => null,
            ]),
        ]);

        // Use lower thresholds to test fallback path
        config()->set('opencode.session.stale_threshold_ms', 120_000);
        config()->set('opencode.session.fallback_idle_threshold_ms', 50_000);

        $session = createTestSession();
        $assessment = $this->manager->assess($session);

        expect($assessment->state)->toBe(SessionState::Idle);
        expect($assessment->reason)->toContain('state is null');

        MockClient::destroyGlobal();
    });

    test('returns Active for recently updated session', function (): void {
        $recentTimestamp = (int) (microtime(true) * 1000) - 10_000; // 10s ago

        MockClient::global([
            MockResponse::make([
                'id' => 'ses_123',
                'title' => 'Test',
                'time' => ['updated' => $recentTimestamp],
                'state' => 'generating',
            ]),
        ]);

        $session = createTestSession();
        $assessment = $this->manager->assess($session);

        expect($assessment->state)->toBe(SessionState::Active);
        expect($assessment->isTerminal())->toBeFalse();

        MockClient::destroyGlobal();
    });
});

describe('lifecycle transitions', function (): void {
    test('activate sets status and fires event', function (): void {
        Event::fake([SessionActivated::class]);
        $session = createTestSession();

        $this->manager->activate($session);

        expect($session->fresh()->status)->toBe(SessionStatus::Active);
        Event::assertDispatched(SessionActivated::class);
    });

    test('markIdle sets status and fires event', function (): void {
        Event::fake([SessionBecameIdle::class]);
        $session = createTestSession(['status' => SessionStatus::Active->value]);

        $this->manager->markIdle($session);

        expect($session->fresh()->status)->toBe(SessionStatus::Idle);
        Event::assertDispatched(SessionBecameIdle::class);
    });

    test('complete sets status and fires event', function (): void {
        Event::fake([SessionCompleted::class]);
        $session = createTestSession(['status' => SessionStatus::Active->value]);

        $this->manager->complete($session);

        $fresh = $session->fresh();
        expect($fresh->status)->toBe(SessionStatus::Completed);
        expect($fresh->ended_at)->not->toBeNull();
        Event::assertDispatched(SessionCompleted::class);
    });

    test('fail sets status, error message, and fires event', function (): void {
        Event::fake([SessionFailed::class]);
        $session = createTestSession(['status' => SessionStatus::Active->value]);

        $this->manager->fail($session, 'Something went wrong');

        $fresh = $session->fresh();
        expect($fresh->status)->toBe(SessionStatus::Failed);
        expect($fresh->error_message)->toBe('Something went wrong');
        expect($fresh->ended_at)->not->toBeNull();
        Event::assertDispatched(SessionFailed::class);
    });

    test('interrupt sets status and fires event', function (): void {
        Event::fake([SessionInterrupted::class]);
        $session = createTestSession(['status' => SessionStatus::Active->value]);

        $this->manager->interrupt($session);

        expect($session->fresh()->status)->toBe(SessionStatus::Interrupted);
        Event::assertDispatched(SessionInterrupted::class);
    });

    test('recover sets status and fires event', function (): void {
        Event::fake([SessionRecovered::class]);
        $session = createTestSession(['status' => SessionStatus::Interrupted->value]);

        $this->manager->recover($session);

        $fresh = $session->fresh();
        expect($fresh->status)->toBe(SessionStatus::Recovered);
        expect($fresh->recovery_attempts)->toBe(1);
        Event::assertDispatched(SessionRecovered::class);
    });
});

describe('model status helpers', function (): void {
    test('isIdle returns true for idle sessions', function (): void {
        $session = createTestSession(['status' => SessionStatus::Idle->value]);
        expect($session->isIdle())->toBeTrue();
        expect($session->isActive())->toBeFalse();
    });

    test('isActive returns true for active sessions', function (): void {
        $session = createTestSession(['status' => SessionStatus::Active->value]);
        expect($session->isActive())->toBeTrue();
        expect($session->isIdle())->toBeFalse();
    });

    test('isTerminal returns true for completed and failed', function (): void {
        $completed = createTestSession(['status' => SessionStatus::Completed->value, 'ended_at' => now()]);
        $failed = createTestSession(['status' => SessionStatus::Failed->value, 'ended_at' => now()]);
        $active = createTestSession(['status' => SessionStatus::Active->value]);

        expect($completed->isTerminal())->toBeTrue();
        expect($failed->isTerminal())->toBeTrue();
        expect($active->isTerminal())->toBeFalse();
    });
});

/**
 * Helper to create a test session in the database.
 *
 * @param  array<string, mixed>  $attributes
 */
function createTestSession(array $attributes = []): OpenCodeSession
{
    return OpenCodeSession::query()->create(array_merge([
        'sessionable_id' => 1,
        'sessionable_type' => 'App\Models\Task',
        'session_id' => 'ses_'.uniqid(),
        'workspace' => '/home/nckrtl/projects/test',
        'provider' => 'test-provider',
        'model' => 'test-model',
        'status' => SessionStatus::Created->value,
        'recovery_attempts' => 0,
        'started_at' => now(),
    ], $attributes));
}
