<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use HardImpact\OpenCode\Data\MessageWithParts;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetMessages extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $id,
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/session/%s/message', $this->id);
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'directory' => $this->directory,
        ]);
    }

    /** @return MessageWithParts[] */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            MessageWithParts::fromResponse(...),
            (array) $response->json(),
        );
    }
}
