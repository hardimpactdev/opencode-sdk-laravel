<?php

use HardImpact\OpenCode\Models\Task;
use HardImpact\OpenCode\Services\TaskCreationService;
use HardImpact\OpenCode\Services\TaskRefinementService;
use HardImpact\OpenCode\Validation\SchemaValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->creationService = new TaskCreationService(
        new TaskRefinementService,
        new SchemaValidator,
    );
});

test('createDirectTask creates unrefined task with bypass flag', function () {
    $task = $this->creationService->createDirectTask(
        prompt: 'Build a feature',
        assignee: 'coder',
        projectId: 7,
    );

    expect($task)->toBeInstanceOf(Task::class);
    expect($task->title)->toBe('Build a feature');
    expect($task->status)->toBe(Task::STATUS_PENDING);
    expect($task->assignee)->toBe('coder');
    expect($task->project_id)->toBe(7);
    expect($task->refined)->toBeFalse();
    expect($task->bypass_refinement)->toBeTrue();
    expect($task->isClaimable())->toBeTrue();
});

test('refineAndCreate creates single refined task', function () {
    $tasks = $this->creationService->refineAndCreate(
        prompt: 'Implement password reset',
        assignee: 'coder',
        projectId: 7,
    );

    expect($tasks)->toHaveCount(1);

    $task = $tasks[0];
    expect($task)->toBeInstanceOf(Task::class);
    expect($task->refined)->toBeTrue();
    expect($task->bypass_refinement)->toBeFalse();
    expect($task->isClaimable())->toBeTrue();
    expect($task->refinement_output)->not->toBeNull();
});

test('refineAndCreate creates multiple tasks with dependencies', function () {
    $tasks = $this->creationService->refineAndCreate(
        prompt: 'Build authentication system and create admin dashboard',
        assignee: 'coder',
        projectId: 7,
    );

    expect($tasks)->toHaveCount(2);

    [$first, $second] = $tasks;

    expect($first->blockedBy)->toHaveCount(0);
    expect($second->blockedBy)->toHaveCount(1);
    expect($second->blockedBy->first()->id)->toBe($first->id);

    expect($first->isClaimable())->toBeTrue();
    expect($second->isClaimable())->toBeFalse();
});

test('refineAndCreate with bypass flag creates direct task', function () {
    $tasks = $this->creationService->refineAndCreate(
        prompt: 'Build a feature',
        assignee: 'coder',
        projectId: 7,
        bypassRefinement: true,
    );

    expect($tasks)->toHaveCount(1);

    $task = $tasks[0];
    expect($task->bypass_refinement)->toBeTrue();
    expect($task->refined)->toBeFalse();
});

test('refineAndCreate throws exception when refinement validation fails', function () {
    $this->expectException(\InvalidArgumentException::class);

    $service = new TaskCreationService(
        new class extends TaskRefinementService
        {
            public function refine(string $prompt): array
            {
                return [
                    ['invalid' => 'data'],
                ];
            }
        },
        new SchemaValidator,
    );

    $service->refineAndCreate(
        prompt: 'Any prompt',
        assignee: 'coder',
        projectId: 7,
    );
});

test('refineAndCreate persists tasks atomically', function () {
    $tasks = $this->creationService->refineAndCreate(
        prompt: 'Task A and Task B',
        assignee: 'coder',
        projectId: 7,
    );

    expect(Task::count())->toBe(2);

    foreach ($tasks as $task) {
        expect($task->fresh())->not->toBeNull();
        expect($task->exists)->toBeTrue();
    }
});

test('refineAndCreate links dependencies correctly in database', function () {
    $tasks = $this->creationService->refineAndCreate(
        prompt: 'First feature and second feature',
        assignee: 'coder',
        projectId: 7,
    );

    $second = $tasks[1];
    $dependencies = $second->blockedBy()->pluck('task_dependencies.blocked_by_task_id');

    expect($dependencies->toArray())->toContain($tasks[0]->id);
});

test('refined tasks have complete markdown description', function () {
    $tasks = $this->creationService->refineAndCreate(
        prompt: 'Build API',
        assignee: 'coder',
        projectId: 7,
    );

    $task = $tasks[0];
    expect($task->description)->toContain('## Objective');
    expect($task->description)->toContain('## Acceptance Criteria');
    expect($task->description)->toContain('## Deliverables');
});
