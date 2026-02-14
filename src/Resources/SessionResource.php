<?php

namespace HardImpact\OpenCode\Resources;

use HardImpact\OpenCode\Data\MessageWithParts;
use HardImpact\OpenCode\Data\Session;
use HardImpact\OpenCode\Enums\PartType;
use HardImpact\OpenCode\Requests\Sessions\AbortSession;
use HardImpact\OpenCode\Requests\Sessions\CreateSession;
use HardImpact\OpenCode\Requests\Sessions\DeleteSession;
use HardImpact\OpenCode\Requests\Sessions\GetMessages;
use HardImpact\OpenCode\Requests\Sessions\GetSession;
use HardImpact\OpenCode\Requests\Sessions\InitSession;
use HardImpact\OpenCode\Requests\Sessions\ListSessions;
use HardImpact\OpenCode\Requests\Sessions\RevertSession;
use HardImpact\OpenCode\Requests\Sessions\RunCommand;
use HardImpact\OpenCode\Requests\Sessions\SendMessage;
use HardImpact\OpenCode\Requests\Sessions\SendMessageAsync;
use HardImpact\OpenCode\Requests\Sessions\ShareSession;
use HardImpact\OpenCode\Requests\Sessions\SummarizeSession;
use HardImpact\OpenCode\Requests\Sessions\UnrevertSession;
use HardImpact\OpenCode\Requests\Sessions\UnshareSession;
use HardImpact\OpenCode\Requests\Sessions\UpdateSession;
use Saloon\Http\BaseResource;

class SessionResource extends BaseResource
{
    public function create(string $directory, ?string $title = null, ?string $parentID = null): Session
    {
        return $this->connector->send(
            new CreateSession($directory, $title, $parentID)
        )->dto();
    }

    /** @return Session[] */
    public function list(): array
    {
        return $this->connector->send(new ListSessions)->dto();
    }

    public function get(string $id, ?string $directory = null): Session
    {
        return $this->connector->send(new GetSession($id, $directory))->dto();
    }

    public function update(string $id, ?string $title = null): Session
    {
        return $this->connector->send(new UpdateSession($id, $title))->dto();
    }

    public function delete(string $id): bool
    {
        return $this->connector->send(new DeleteSession($id))->successful();
    }

    public function abort(string $id): bool
    {
        return $this->connector->send(new AbortSession($id))->successful();
    }

    public function sendMessage(
        string $id,
        string $providerID,
        string $modelID,
        string $text,
        ?string $directory = null,
        ?string $messageID = null,
        ?string $system = null,
        ?array $tools = null,
    ): MessageWithParts {
        $parts = [['type' => 'text', 'text' => $text]];

        return $this->connector->send(
            new SendMessage($id, $providerID, $modelID, $parts, $directory, $messageID, $system, $tools)
        )->dto();
    }

    public function sendMessageWithParts(
        string $id,
        string $providerID,
        string $modelID,
        array $parts,
        ?string $directory = null,
        ?string $messageID = null,
        ?string $system = null,
        ?array $tools = null,
    ): MessageWithParts {
        return $this->connector->send(
            new SendMessage($id, $providerID, $modelID, $parts, $directory, $messageID, $system, $tools)
        )->dto();
    }

    public function sendMessageAsync(
        string $id,
        string $providerID,
        string $modelID,
        string $text,
        ?string $directory = null,
        ?string $messageID = null,
        ?string $system = null,
        ?array $tools = null,
    ): bool {
        $parts = [['type' => 'text', 'text' => $text]];

        return $this->connector->send(
            new SendMessageAsync($id, $providerID, $modelID, $parts, $directory, $messageID, $system, $tools)
        )->successful();
    }

    /** @return MessageWithParts[] */
    public function messages(string $id, ?string $directory = null): array
    {
        return $this->connector->send(new GetMessages($id, $directory))->dto();
    }

    public function init(
        string $id,
        string $messageID,
        string $modelID,
        string $providerID,
        ?string $directory = null,
    ): bool {
        return $this->connector->send(
            new InitSession($id, $messageID, $modelID, $providerID, $directory)
        )->successful();
    }

    public function summarize(
        string $id,
        string $modelID,
        string $providerID,
        ?string $directory = null,
    ): bool {
        return $this->connector->send(
            new SummarizeSession($id, $modelID, $providerID, $directory)
        )->successful();
    }

    public function revert(
        string $id,
        string $messageID,
        ?string $directory = null,
        ?string $partID = null,
    ): Session {
        return $this->connector->send(
            new RevertSession($id, $messageID, $directory, $partID)
        )->dto();
    }

    public function unrevert(string $id, ?string $directory = null): Session
    {
        return $this->connector->send(new UnrevertSession($id, $directory))->dto();
    }

    public function command(
        string $id,
        string $command,
        string $arguments = '',
        ?string $directory = null,
        ?string $agent = null,
        ?string $messageID = null,
        ?string $model = null,
    ): MessageWithParts {
        return $this->connector->send(
            new RunCommand($id, $command, $arguments, $directory, $agent, $messageID, $model)
        )->dto();
    }

    public function share(string $id): Session
    {
        return $this->connector->send(new ShareSession($id))->dto();
    }

    public function unshare(string $id): Session
    {
        return $this->connector->send(new UnshareSession($id))->dto();
    }

    /**
     * Check if a session is idle (not updated within threshold).
     */
    public function isIdle(string $id, ?string $directory = null, int $thresholdMs = 120_000): bool
    {
        $session = $this->get($id, $directory);
        $updatedAt = $session->time['updated'] ?? null;

        if (! is_numeric($updatedAt)) {
            return false;
        }

        $nowMs = (int) (microtime(true) * 1000);
        $ageMs = $nowMs - (int) $updatedAt;

        return $ageMs > $thresholdMs;
    }

    /**
     * Check if a session is active (updated within threshold).
     */
    public function isActive(string $id, ?string $directory = null, int $thresholdMs = 120_000): bool
    {
        return ! $this->isIdle($id, $directory, $thresholdMs);
    }

    /**
     * Get text content from the last message in a session.
     */
    public function getLastMessageText(string $id, ?string $directory = null): string
    {
        $messages = $this->messages($id, $directory);

        if (empty($messages)) {
            return '';
        }

        $lastMessage = $messages[array_key_last($messages)];

        $text = '';
        foreach ($lastMessage->parts as $part) {
            if ($part->type === PartType::Text) {
                $text .= $part->text ?? '';
            }
        }

        return $text;
    }

    /**
     * Check if the last message in a session contains completion indicators.
     *
     * @param  array<string>  $patterns  Regex patterns to match (default: common completion patterns)
     */
    public function hasCompletionIndicators(string $id, ?string $directory = null, array $patterns = []): bool
    {
        if (empty($patterns)) {
            $patterns = [
                '/completed successfully/i',
                '/task.*complete/i',
                '/Summary:/i',
            ];
        }

        $text = $this->getLastMessageText($id, $directory);

        if (empty($text)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, '') === false) {
                throw new \InvalidArgumentException("Invalid regex pattern: {$pattern}");
            }

            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }
}
