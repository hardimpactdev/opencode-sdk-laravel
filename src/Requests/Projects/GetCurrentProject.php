<?php

namespace HardImpact\OpenCode\Requests\Projects;

use HardImpact\OpenCode\Data\Project;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetCurrentProject extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/project/current';
    }

    protected function defaultQuery(): array
    {
        return array_filter(['directory' => $this->directory]);
    }

    public function createDtoFromResponse(Response $response): Project
    {
        return Project::from($response->json());
    }
}
