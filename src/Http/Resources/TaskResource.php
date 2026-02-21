<?php

namespace HardImpact\OpenCode\Http\Resources;

use HardImpact\OpenCode\Models\Task;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Task $this */
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'assignee' => $this->assignee,
            'project_id' => $this->project_id,
            'refined' => $this->refined,
            'bypass_refinement' => $this->bypass_refinement,
            'is_claimable' => $this->isClaimable(),
            'blocked_by' => $this->blockedBy->pluck('id'),
            'blocking' => $this->blocking->pluck('id'),
            'refinement_output' => $this->refinement_output,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}