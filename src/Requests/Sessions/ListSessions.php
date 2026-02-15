<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\Session;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListSessions extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/session';
    }

    /** @return Session[] */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $data) => Session::from($data),
            (array) $response->json(),
        );
    }
}
