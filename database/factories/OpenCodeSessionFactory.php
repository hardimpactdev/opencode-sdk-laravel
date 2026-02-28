<?php

declare(strict_types=1);

namespace HardImpact\OpenCode\Database\Factories;

use HardImpact\OpenCode\Enums\SessionStatus;
use HardImpact\OpenCode\Models\OpenCodeSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpenCodeSession>
 */
class OpenCodeSessionFactory extends Factory
{
    protected $model = OpenCodeSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => 'ses_'.$this->faker->unique()->uuid(),
            'workspace' => '/home/nckrtl/projects/test',
            'provider' => 'kimi-for-coding',
            'model' => 'k2p5',
            'status' => SessionStatus::Created->value,
            'recovery_attempts' => 0,
            'started_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Active->value,
        ]);
    }

    public function interrupted(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Interrupted->value,
        ]);
    }

    public function recovered(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Recovered->value,
            'recovery_attempts' => 1,
            'last_recovery_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Completed->value,
            'ended_at' => now(),
        ]);
    }

    public function failed(string $error = 'Session not found'): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Failed->value,
            'error_message' => $error,
            'ended_at' => now(),
        ]);
    }
}
