<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateSession extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $directory,
        protected ?string $title = null,
        protected ?string $parentID = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/session';
    }

    protected function defaultQuery(): array
    {
        return ['directory' => $this->directory];
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'title' => $this->title,
            'parentID' => $this->parentID,
        ]);
    }
}
