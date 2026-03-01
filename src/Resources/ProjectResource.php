<?php

namespace HardImpact\OpenCode\Resources;

use HardImpact\OpenCode\Data\Project;
use HardImpact\OpenCode\Requests\Projects\GetCurrentProject;
use HardImpact\OpenCode\Requests\Projects\ListProjects;
use HardImpact\OpenCode\Requests\Projects\UpdateProject;
use Saloon\Http\BaseResource;

class ProjectResource extends BaseResource
{
    /** @return Project[] */
    public function list(?string $directory = null): array
    {
        return $this->connector->send(new ListProjects($directory))->throw()->dto();
    }

    public function current(?string $directory = null): Project
    {
        return $this->connector->send(new GetCurrentProject($directory))->throw()->dto();
    }

    public function update(
        string $id,
        ?string $name = null,
        ?array $icon = null,
        ?array $commands = null,
        ?array $sandboxes = null,
        ?string $directory = null,
    ): Project {
        return $this->connector->send(
            new UpdateProject($id, $name, $icon, $commands, $sandboxes, $directory)
        )->throw()->dto();
    }
}
