<?php

namespace HardImpact\OpenCode\Http\Controllers;

use HardImpact\OpenCode\Http\Resources\TaskResource;
use HardImpact\OpenCode\Services\TaskCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TasksController
{
    public function __construct(
        private TaskCreationService $creationService,
    ) {}

    public function refineAndCreate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'prompt' => ['required', 'string', 'min:5'],
                'assignee' => ['required', 'string'],
                'project_id' => ['required', 'integer', 'min:1'],
                'bypass_refinement' => ['boolean'],
            ]);

            $bypassRefinement = $validated['bypass_refinement'] ?? false;

            $tasks = $this->creationService->refineAndCreate(
                prompt: $validated['prompt'],
                assignee: $validated['assignee'],
                projectId: $validated['project_id'],
                bypassRefinement: $bypassRefinement,
            );

            return response()->json([
                'success' => true,
                'message' => count($tasks) === 1
                    ? 'Task created successfully'
                    : count($tasks) . ' tasks created successfully',
                'data' => TaskResource::collection($tasks),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refinement validation failed',
                'errors' => [
                    'refinement' => [$e->getMessage()],
                ],
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task(s)',
                'errors' => [
                    'general' => [$e->getMessage()],
                ],
            ], 500);
        }
    }

    public function createDirect(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'prompt' => ['required', 'string', 'min:5'],
                'assignee' => ['required', 'string'],
                'project_id' => ['required', 'integer', 'min:1'],
            ]);

            $task = $this->creationService->createDirectTask(
                prompt: $validated['prompt'],
                assignee: $validated['assignee'],
                projectId: $validated['project_id'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Task created directly (bypassed refinement)',
                'data' => new TaskResource($task),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'errors' => [
                    'general' => [$e->getMessage()],
                ],
            ], 500);
        }
    }
}