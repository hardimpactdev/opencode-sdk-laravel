<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class UnshareSession extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $id,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}/share";
    }
}
