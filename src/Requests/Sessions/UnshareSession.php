<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\Session;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

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

    public function createDtoFromResponse(Response $response): Session
    {
        return Session::from($response->json());
    }
}
