<?php

namespace HardImpact\OpenCode\Resources;

use HardImpact\OpenCode\Data\Provider;
use HardImpact\OpenCode\Requests\Providers\GetProviders;
use Saloon\Http\BaseResource;

class ProviderResource extends BaseResource
{
    public function list(): array
    {
        return $this->connector->send(new GetProviders)->dto();
    }
}
