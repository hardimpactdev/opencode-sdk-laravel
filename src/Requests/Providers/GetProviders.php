<?php

namespace HardImpact\OpenCode\Requests\Providers;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProviders extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/config/providers';
    }
}
