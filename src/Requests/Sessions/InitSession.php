<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class InitSession extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $id,
        protected string $messageID,
        protected string $modelID,
        protected string $providerID,
        protected ?string $directory = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/session/%s/init', $this->id);
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
            'messageID' => $this->messageID,
            'modelID' => $this->modelID,
            'providerID' => $this->providerID,
        ];
    }
}
