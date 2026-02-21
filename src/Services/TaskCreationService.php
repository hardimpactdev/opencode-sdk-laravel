<?php

namespace HardImpact\OpenCode\Services;

use HardImpact\OpenCode\Models\Task;
use HardImpact\OpenCode\Validation\SchemaValidator;
use Illuminate\Support\Facades\DB;

class TaskCreationService
{
    public function __construct(
        private TaskRefinementService $refinementService,
        private SchemaValidator $validator,
    ) {}

    public function refineAndCreate(
        string $prompt,
        string $assignee,
        int $projectId,
        bool $bypassRefinement = false,
    ): array {
        if ($bypassRefinement) {
            return [$this->createDirectTask($prompt, $assignee, $projectId)];
        }

        $refinedSpecs = $this->refinementService->refine($prompt);

        $tasks = [];
        DB::transaction(function () use ($refinedSpecs, $assignee, $projectId, &$tasks) {
            $createdTasks = [];

            foreach ($refinedSpecs as $index => $spec) {
                $validation = $this->validator->validate($spec);

                if (! $validation->isValid()) {
                    throw new \InvalidArgumentException(
                        "Task {$index} validation failed: ".$validation->getErrorMessage()
                    );
                }

                $task = Task::create([
                    'title' => $spec['title'] ?? 'Untitled Task',
                    'description' => $this->buildDescription($spec),
                    'status' => Task::STATUS_PENDING,
                    'assignee' => $assignee,
                    'project_id' => $projectId,
                    'refined' => true,
                    'refinement_output' => $spec,
                    'bypass_refinement' => false,
                ]);

                $createdTasks[$index] = $task;
            }

            foreach ($refinedSpecs as $index => $spec) {
                if (! empty($spec['blocked_by'])) {
                    foreach ($spec['blocked_by'] as $blockedByIndex) {
                        if (isset($createdTasks[$blockedByIndex])) {
                            $createdTasks[$index]->blockedBy()->attach($createdTasks[$blockedByIndex]->id);
                        }
                    }
                }
            }

            $tasks = array_values($createdTasks);
        });

        return $tasks;
    }

    public function createDirectTask(
        string $prompt,
        string $assignee,
        int $projectId,
    ): Task {
        return Task::create([
            'title' => substr($prompt, 0, 255),
            'description' => $prompt,
            'status' => Task::STATUS_PENDING,
            'assignee' => $assignee,
            'project_id' => $projectId,
            'refined' => false,
            'refinement_output' => null,
            'bypass_refinement' => true,
        ]);
    }

    private function buildDescription(array $spec): string
    {
        $sectionTitles = [
            'objective' => 'Objective',
            'context' => 'Context',
            'acceptance_criteria' => 'Acceptance Criteria',
            'deliverables' => 'Deliverables',
            'implementation_details' => 'Implementation Details',
            'how_to_verify' => 'How To Verify',
            'definition_of_done' => 'Definition Of Done',
            'if_blocked' => 'If Blocked',
        ];

        $parts = [];
        foreach ($sectionTitles as $section => $title) {
            if (! empty($spec[$section])) {
                $parts[] = "## {$title}\n\n{$spec[$section]}";
            }
        }

        return implode("\n\n", $parts);
    }
}
