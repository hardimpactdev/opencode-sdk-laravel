<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class AbortSession extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $id,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}/abort";
    }
}
