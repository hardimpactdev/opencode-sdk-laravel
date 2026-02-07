<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\MessageWithParts;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class RunCommand extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $id,
        protected string $command,
        protected string $arguments = '',
        protected ?string $directory = null,
        protected ?string $agent = null,
        protected ?string $messageID = null,
        protected ?string $model = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}/command";
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
            'command' => $this->command,
            'arguments' => $this->arguments,
            'agent' => $this->agent,
            'messageID' => $this->messageID,
            'model' => $this->model,
        ]);
    }

    public function createDtoFromResponse(Response $response): MessageWithParts
    {
        return MessageWithParts::fromResponse($response->json());
    }
}
