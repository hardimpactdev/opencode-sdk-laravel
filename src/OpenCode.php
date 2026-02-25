<?php

namespace HardImpact\OpenCode;

use HardImpact\OpenCode\Resources\EventResource;
use HardImpact\OpenCode\Resources\ProjectResource;
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
        ];
    }

    public function sessions(): SessionResource
    {
        return $this->sessions ??= new SessionResource($this);
    }

    public function events(): EventResource
    {
        return $this->events ??= new EventResource($this);
    }

    public function questions(): QuestionResource
    {
        return $this->questions ??= new QuestionResource($this);
    }

    public function providers(): ProviderResource
    {
        return $this->providers ??= new ProviderResource($this);
    }

    public function projects(): ProjectResource
    {
        return $this->projects ??= new ProjectResource($this);
    }

    private ?SessionResource $sessions = null;

    private ?EventResource $events = null;

    private ?QuestionResource $questions = null;

    private ?ProviderResource $providers = null;

    private ?ProjectResource $projects = null;
}
