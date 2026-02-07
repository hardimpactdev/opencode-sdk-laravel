<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SendMessageAsync extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $id,
        protected string $providerID,
        protected string $modelID,
        protected array $parts,
        protected ?string $directory = null,
        protected ?string $messageID = null,
        protected ?string $system = null,
        protected ?array $tools = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/session/{$this->id}/prompt_async";
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
            'model' => [
                'providerID' => $this->providerID,
                'modelID' => $this->modelID,
            ],
            'parts' => $this->parts,
            'messageID' => $this->messageID,
            'system' => $this->system,
            'tools' => $this->tools,
        ]);
    }
}
