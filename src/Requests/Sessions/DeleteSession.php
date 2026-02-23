<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteSession extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $id,
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}";
    }

    protected function defaultQuery(): array
    {
        return array_filter(['directory' => $this->directory]);
    }
}
