<?php

namespace HardImpact\OpenCode\Validation;

class ValidationResult
{
    public function __construct(
        private array $errors = [],
    ) {}

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorMessage(): string
    {
        if ($this->isValid()) {
            return '';
        }

        $messages = array_map(
            fn ($error) => "{$error['field']}: {$error['reason']}",
            $this->errors
        );

        return implode('; ', $messages);
    }
}