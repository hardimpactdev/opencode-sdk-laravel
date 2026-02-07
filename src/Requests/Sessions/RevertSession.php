<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\Session;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class RevertSession extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $id,
        protected string $messageID,
        protected ?string $directory = null,
        protected ?string $partID = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}/revert";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'directory' => $this->directory,
        ]);
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'messageID' => $this->messageID,
            'partID' => $this->partID,
        ]);
    }

    public function createDtoFromResponse(Response $response): Session
    {
        return Session::from($response->json());
    }
}
