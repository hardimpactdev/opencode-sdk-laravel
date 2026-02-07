<?php

namespace HardImpact\OpenCode\Requests\Questions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AnswerQuestion extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $sessionId,
        protected string $permissionId,
        protected string $answer = 'once',
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->sessionId}/permissions/{$this->permissionId}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'directory' => $this->directory,
        ]);
    }

    protected function defaultBody(): array
    {
        return [
            'response' => $this->answer,
        ];
    }
}
