<?php

namespace HardImpact\OpenCode\Requests\Questions;

use HardImpact\OpenCode\Data\Question;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListQuestions extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/question';
    }

    /** @return Question[] */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $data) => Question::from($data),
            (array) $response->json(),
        );
    }
}
