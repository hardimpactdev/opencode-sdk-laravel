<?php

namespace HardImpact\OpenCode;

use HardImpact\OpenCode\Resources\EventResource;
use HardImpact\OpenCode\Resources\ProviderResource;
use HardImpact\OpenCode\Resources\QuestionResource;
use HardImpact\OpenCode\Resources\SessionResource;
use Saloon\Http\Connector;

class OpenCode extends Connector
{
    public function __construct(
        protected ?string $baseUrl = null,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl ?? config('opencode.base_url', 'http://localhost:4096');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function sessions(): SessionResource
    {
        return new SessionResource($this);
    }

    public function events(): EventResource
    {
        return new EventResource($this);
    }

    public function questions(): QuestionResource
    {
        return new QuestionResource($this);
    }

    public function providers(): ProviderResource
    {
        return new ProviderResource($this);
    }
}
