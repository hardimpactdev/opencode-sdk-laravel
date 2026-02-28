<?php

declare(strict_types=1);

namespace HardImpact\OpenCode;

use HardImpact\OpenCode\Data\SessionAssessment;
use HardImpact\OpenCode\Enums\SessionState;
use HardImpact\OpenCode\Events\SessionActivated;
use HardImpact\OpenCode\Events\SessionBecameIdle;
use HardImpact\OpenCode\Events\SessionCompleted;
use HardImpact\OpenCode\Events\SessionFailed;
use HardImpact\OpenCode\Events\SessionInterrupted;
use HardImpact\OpenCode\Events\SessionRecovered;
use HardImpact\OpenCode\Models\OpenCodeSession;
use Illuminate\Support\Facades\Config;
use Throwable;

final class SessionManager
{
    /**
     * Assess the current state of a session by querying the OpenCode API.
     *
     * Detection chain:
     * 1. API session not found → Missing
     * 2. File changes + stale > threshold → Completed
     * 3. Completion indicators in last message → Completed
     * 4. API idle check (stale > threshold) → Idle
     * 5. state=null + stale > fallback threshold → Idle
     * 6. Otherwise → Active
     *
     * @param  array<string>  $completionPatterns  Regex patterns to match in last message
     */
    public function assess(OpenCodeSession $session, array $completionPatterns = []): SessionAssessment
    {
        $client = $this->resolveClient($session);
        $sessions = $client->sessions();
        $workspace = $session->workspace;

        /** @var int $staleThreshold */
        $staleThreshold = Config::get('opencode.session.stale_threshold_ms', 120_000);
        /** @var int $fallbackIdleThreshold */
        $fallbackIdleThreshold = Config::get('opencode.session.fallback_idle_threshold_ms', 300_000);

        // 1. Session not found → Missing
        try {
            $apiSession = $sessions->get($session->session_id, $workspace);
        } catch (Throwable) {
            return new SessionAssessment(
                state: SessionState::Missing,
                reason: sprintf('Session %s not found in API', $session->session_id),
            );
        }

        // Calculate age
        $updatedAt = $apiSession->time['updated'] ?? null;
        $ageMs = null;
        if (is_numeric($updatedAt)) {
            $nowMs = (int) (microtime(true) * 1000);
            $ageMs = $nowMs - (int) $updatedAt;
        }

        // 2. File changes + stale → Completed
        $hasFileChanges = isset($apiSession->summary) && (
            ($apiSession->summary['files'] ?? 0) > 0 ||
            ($apiSession->summary['additions'] ?? 0) > 0 ||
            ($apiSession->summary['deletions'] ?? 0) > 0
        );

        if ($hasFileChanges && $ageMs !== null && $ageMs > $staleThreshold) {
            return new SessionAssessment(
                state: SessionState::Completed,
                reason: sprintf('File changes detected and session stale for %dms', $ageMs),
            );
        }

        // 3. Completion indicators in last message → Completed
        if ($completionPatterns !== []) {
            try {
                $lastMessageText = $this->getLastMessageText($sessions, $session->session_id, $workspace);

                if ($lastMessageText !== '' && $this->matchesPatterns($lastMessageText, $completionPatterns)) {
                    return new SessionAssessment(
                        state: SessionState::Completed,
                        reason: 'Completion indicator found in last message',
                    );
                }
            } catch (Throwable) {
                // Message check failed — continue to next heuristic
            }
        }

        // 4. Stale > threshold → Idle
        if ($ageMs !== null && $ageMs > $staleThreshold) {
            return new SessionAssessment(
                state: SessionState::Idle,
                reason: sprintf('Session stale for %dms (threshold: %dms)', $ageMs, $staleThreshold),
            );
        }

        // 5. state=null + stale > fallback threshold → Idle
        if ($apiSession->state === null && $ageMs !== null && $ageMs > $fallbackIdleThreshold) {
            return new SessionAssessment(
                state: SessionState::Idle,
                reason: sprintf('Session state is null and stale for %dms (fallback threshold: %dms)', $ageMs, $fallbackIdleThreshold),
            );
        }

        // 6. Active
        return new SessionAssessment(state: SessionState::Active);
    }

    public function activate(OpenCodeSession $session): void
    {
        $session->update(['status' => \HardImpact\OpenCode\Enums\SessionStatus::Active]);
        event(new SessionActivated($session));
    }

    public function markIdle(OpenCodeSession $session): void
    {
        $session->markAsIdle();
        event(new SessionBecameIdle($session));
    }

    public function complete(OpenCodeSession $session): void
    {
        $session->markAsCompleted();
        event(new SessionCompleted($session));
    }

    public function fail(OpenCodeSession $session, string $error): void
    {
        $session->markAsFailed($error);
        event(new SessionFailed($session));
    }

    public function interrupt(OpenCodeSession $session): void
    {
        $session->markAsInterrupted();
        event(new SessionInterrupted($session));
    }

    public function recover(OpenCodeSession $session): void
    {
        $session->markAsRecovered();
        event(new SessionRecovered($session));
    }

    private function resolveClient(OpenCodeSession $session): OpenCode
    {
        $baseUrl = $session->server_url ?? Config::get('opencode.base_url', 'http://localhost:4096');

        return new OpenCode($baseUrl);
    }

    /**
     * @param  array<string>  $patterns
     */
    private function matchesPatterns(string $text, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, '') === false) {
                continue;
            }

            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    private function getLastMessageText(
        \HardImpact\OpenCode\Resources\SessionResource $sessions,
        string $sessionId,
        string $workspace,
    ): string {
        $messages = $sessions->messages($sessionId, $workspace);

        if ($messages === []) {
            return '';
        }

        $lastMessage = $messages[array_key_last($messages)];

        $text = '';
        foreach ($lastMessage->parts as $part) {
            if ($part->type === \HardImpact\OpenCode\Enums\PartType::Text) {
                $text .= $part->text ?? '';
            }
        }

        return $text;
    }
}
