<?php

namespace HardImpact\OpenCode\Requests\Questions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListQuestions extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/question';
    }
}
