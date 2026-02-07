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
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}/message";
    }

    /** @return MessageWithParts[] */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $data) => MessageWithParts::fromResponse($data),
            $response->json() ?? [],
        );
    }
}
