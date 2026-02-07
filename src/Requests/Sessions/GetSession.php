<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\Session;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetSession extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $id,
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'directory' => $this->directory,
        ]);
    }

    public function createDtoFromResponse(Response $response): Session
    {
        return Session::from($response->json());
    }
}
