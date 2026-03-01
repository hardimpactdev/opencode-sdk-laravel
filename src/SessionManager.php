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
use Saloon\Exceptions\Request\RequestException;

final class SessionManager
{
    /**
     * Assess the current state of a session by querying the OpenCode API.
     *
     * Detection chain:
     * 1. API session not found → Missing
     * 2. API reports terminal state (e.g. "completed") → Completed
     * 3. File changes + stale > threshold → Completed
     * 4. Completion indicators in last message → Completed
     * 5. Known state + stale > threshold → Idle
     * 6. state=null/empty + stale > fallback threshold → Idle
     * 7. Otherwise → Active
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
        } catch (RequestException $e) {
            if ($e->getResponse()->status() === 404) {
                return new SessionAssessment(
                    state: SessionState::Missing,
                    reason: sprintf('Session %s not found in API', $session->session_id),
                );
            }

            throw $e;
        }

        // 2. Explicit API state → short-circuit on terminal states
        if ($apiSession->state !== null && $apiSession->state !== '') {
            $knownState = SessionState::tryFrom($apiSession->state);
            if ($knownState === SessionState::Completed) {
                return new SessionAssessment(
                    state: SessionState::Completed,
                    reason: sprintf('API reports session state as "%s"', $apiSession->state),
                );
            }
        }

        // Calculate age
        $updatedAt = $apiSession->time['updated'] ?? null;
        $ageMs = null;
        if (is_numeric($updatedAt)) {
            $nowMs = (int) (microtime(true) * 1000);
            $ageMs = $nowMs - (int) $updatedAt;
        }

        // 3. File changes + stale → Completed
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

        // 4. Completion indicators in last message → Completed
        if ($completionPatterns !== []) {
            try {
                $lastMessageText = $this->getLastMessageText($sessions, $session->session_id, $workspace);

                if ($lastMessageText !== '' && $this->matchesPatterns($lastMessageText, $completionPatterns)) {
                    return new SessionAssessment(
                        state: SessionState::Completed,
                        reason: 'Completion indicator found in last message',
                    );
                }
            } catch (RequestException $e) {
                if ($e->getResponse()->status() !== 404) {
                    throw $e;
                }
                // 404 means no messages yet — continue to next heuristic
            } catch (\ValueError) {
                // Unknown enum value in message data — skip completion pattern check
                // and fall through to staleness heuristics which don't need message content
            }
        }

        // 5. Known state + stale > threshold → Idle
        if ($apiSession->state !== null && $apiSession->state !== '' && $ageMs !== null && $ageMs > $staleThreshold) {
            return new SessionAssessment(
                state: SessionState::Idle,
                reason: sprintf('Session stale for %dms (threshold: %dms)', $ageMs, $staleThreshold),
            );
        }

        // 6. state=null/empty + stale > fallback threshold → Idle
        if (($apiSession->state === null || $apiSession->state === '') && $ageMs !== null && $ageMs > $fallbackIdleThreshold) {
            return new SessionAssessment(
                state: SessionState::Idle,
                reason: sprintf('Session state is null and stale for %dms (fallback threshold: %dms)', $ageMs, $fallbackIdleThreshold),
            );
        }

        // 7. Active
        return new SessionAssessment(state: SessionState::Active);
    }

    public function activate(OpenCodeSession $session): void
    {
        if ($session->isActive()) {
            return;
        }

        $session->update(['status' => \HardImpact\OpenCode\Enums\SessionStatus::Active]);
        event(new SessionActivated($session));
    }

    public function markIdle(OpenCodeSession $session): void
    {
        if ($session->isIdle()) {
            return;
        }

        $session->markAsIdle();
        event(new SessionBecameIdle($session));
    }

    public function complete(OpenCodeSession $session): void
    {
        if ($session->isTerminal()) {
            return;
        }

        $session->markAsCompleted();
        event(new SessionCompleted($session));
    }

    public function fail(OpenCodeSession $session, string $error): void
    {
        if ($session->isTerminal()) {
            return;
        }

        $session->markAsFailed($error);
        event(new SessionFailed($session));
    }

    public function interrupt(OpenCodeSession $session): void
    {
        if ($session->status === \HardImpact\OpenCode\Enums\SessionStatus::Interrupted) {
            return;
        }

        $session->markAsInterrupted();
        event(new SessionInterrupted($session));
    }

    public function recover(OpenCodeSession $session): void
    {
        if ($session->status !== \HardImpact\OpenCode\Enums\SessionStatus::Interrupted) {
            return;
        }

        $session->markAsRecovered();
        event(new SessionRecovered($session));
    }

    private function resolveClient(OpenCodeSession $session): OpenCode
    {
        if ($session->server_url !== null) {
            return new OpenCode($session->server_url);
        }

        return app(OpenCode::class);
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
