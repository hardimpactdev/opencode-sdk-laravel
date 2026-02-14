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
        return "/session/{$this->id}/message";
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
            fn (array $data) => MessageWithParts::fromResponse($data),
            (array) $response->json(),
        );
    }
}
