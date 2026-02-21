<?php

namespace HardImpact\OpenCode\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'status',
        'assignee',
        'project_id',
        'refined',
        'refinement_output',
        'bypass_refinement',
    ];

    protected $casts = [
        'refined' => 'boolean',
        'bypass_refinement' => 'boolean',
        'refinement_output' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_BLOCKED = 'blocked';

    public function blockedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'blocked_by_task_id'
        );
    }

    public function blocking(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'blocked_by_task_id',
            'task_id'
        );
    }

    public function isClaimable(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        if (! $this->refined && ! $this->bypass_refinement) {
            return false;
        }

        return $this->blockedBy()->count() === 0;
    }

    public function markAsRefined(array $refinementOutput): void
    {
        $this->update([
            'refined' => true,
            'refinement_output' => $refinementOutput,
        ]);
    }

    public function scopeClaimable($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where(function ($q) {
                $q->where('refined', true)
                    ->orWhere('bypass_refinement', true);
            })
            ->whereNotExists(function ($q) {
                $table = $this->getTable();
                $q->selectRaw('1')
                    ->from('task_dependencies')
                    ->whereColumn('task_dependencies.task_id', "{$table}.id");
            });
    }
}