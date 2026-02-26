<?php

namespace HardImpact\OpenCode\Requests\Projects;

use HardImpact\OpenCode\Data\Project;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateProject extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $id,
        protected ?string $name = null,
        protected ?array $icon = null,
        protected ?array $commands = null,
        protected ?array $sandboxes = null,
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/project/%s', $this->id);
    }

    protected function defaultQuery(): array
    {
        return array_filter(['directory' => $this->directory]);
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'name' => $this->name,
            'icon' => $this->icon,
            'commands' => $this->commands,
            'sandboxes' => $this->sandboxes,
        ]);
    }

    public function createDtoFromResponse(Response $response): Project
    {
        return Project::from($response->json());
    }
}
