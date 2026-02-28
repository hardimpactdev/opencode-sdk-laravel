<?php

declare(strict_types=1);

namespace HardImpact\OpenCode\Models;

use HardImpact\OpenCode\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;
use Override;

/**
 * @property int $id
 * @property string $sessionable_id
 * @property string $sessionable_type
 * @property string $session_id
 * @property string|null $server_url
 * @property string $workspace
 * @property string|null $provider
 * @property string|null $model
 * @property SessionStatus $status
 * @property int $recovery_attempts
 * @property \Carbon\Carbon|null $last_recovery_at
 * @property string|null $error_message
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $ended_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class OpenCodeSession extends Model
{
    /** @use HasFactory<\HardImpact\OpenCode\Database\Factories\OpenCodeSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'sessionable_id',
        'sessionable_type',
        'session_id',
        'server_url',
        'workspace',
        'provider',
        'model',
        'status',
        'recovery_attempts',
        'last_recovery_at',
        'error_message',
        'started_at',
        'ended_at',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function sessionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isIdle(): bool
    {
        return $this->status === SessionStatus::Idle;
    }

    public function isActive(): bool
    {
        return $this->status === SessionStatus::Active;
    }

    public function isCompleted(): bool
    {
        return $this->status === SessionStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === SessionStatus::Failed;
    }

    public function isTerminal(): bool
    {
        return $this->isCompleted() || $this->isFailed();
    }

    public function markAsIdle(): void
    {
        $this->update(['status' => SessionStatus::Idle]);
    }

    public function markAsInterrupted(): void
    {
        $this->update(['status' => SessionStatus::Interrupted]);
    }

    public function markAsRecovered(): void
    {
        $this->update([
            'status' => SessionStatus::Recovered,
            'recovery_attempts' => $this->recovery_attempts + 1,
            'last_recovery_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => SessionStatus::Completed,
            'ended_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => SessionStatus::Failed,
            'error_message' => $error,
            'ended_at' => now(),
        ]);
    }

    public function canRecover(): bool
    {
        /** @var int $maxAttempts */
        $maxAttempts = Config::get('opencode.recovery.max_attempts', 2);

        return $this->recovery_attempts < $maxAttempts;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'recovery_attempts' => 'integer',
            'last_recovery_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function newFactory(): \HardImpact\OpenCode\Database\Factories\OpenCodeSessionFactory
    {
        return \HardImpact\OpenCode\Database\Factories\OpenCodeSessionFactory::new();
    }
}
