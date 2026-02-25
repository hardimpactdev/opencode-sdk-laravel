<?php

namespace HardImpact\OpenCode\Requests\Projects;

use HardImpact\OpenCode\Data\Project;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListProjects extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/project';
    }

    protected function defaultQuery(): array
    {
        return array_filter(['directory' => $this->directory]);
    }

    /** @return Project[] */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            Project::from(...),
            (array) $response->json(),
        );
    }
}
