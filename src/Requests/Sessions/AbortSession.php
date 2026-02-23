<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class AbortSession extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $id,
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/session/%s/abort', $this->id);
    }

    protected function defaultQuery(): array
    {
        return array_filter(['directory' => $this->directory]);
    }
}
