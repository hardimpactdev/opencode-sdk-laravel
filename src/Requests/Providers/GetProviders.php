<?php

namespace HardImpact\OpenCode\Requests\Providers;

use HardImpact\OpenCode\Data\Provider;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetProviders extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/config/providers';
    }

    /** @return Provider[] */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $data) => Provider::from($data),
            (array) $response->json(),
        );
    }
}
