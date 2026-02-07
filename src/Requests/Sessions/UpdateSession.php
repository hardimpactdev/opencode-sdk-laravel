<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\Session;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateSession extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $id,
        protected ?string $title = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}";
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'title' => $this->title,
        ]);
    }

    public function createDtoFromResponse(Response $response): Session
    {
        return Session::from($response->json());
    }
}
