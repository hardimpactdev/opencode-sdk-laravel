<?php

namespace HardImpact\OpenCode\Validation;

class SchemaValidator
{
    private array $requiredSections = [
        'title',
        'objective',
        'acceptance_criteria',
        'deliverables',
    ];

    private array $allSections = [
        'title',
        'objective',
        'context',
        'acceptance_criteria',
        'deliverables',
        'implementation_details',
        'how_to_verify',
        'definition_of_done',
        'if_blocked',
        'blocked_by',
    ];

    public function validate(array $spec): ValidationResult
    {
        $errors = [];

        foreach ($this->requiredSections as $section) {
            if (empty($spec[$section])) {
                $errors[] = [
                    'field' => $section,
                    'reason' => "Required section '{$section}' is missing or empty",
                ];
            }
        }

        foreach ($spec as $key => $value) {
            if (! in_array($key, $this->allSections)) {
                $errors[] = [
                    'field' => $key,
                    'reason' => "Unknown field '{$key}' in spec",
                ];
            }
        }

        if (isset($spec['blocked_by']) && ! is_array($spec['blocked_by'])) {
            $errors[] = [
                'field' => 'blocked_by',
                'reason' => 'blocked_by must be an array of task indices',
            ];
        }

        if (isset($spec['title']) && strlen($spec['title']) > 255) {
            $errors[] = [
                'field' => 'title',
                'reason' => 'Title must be 255 characters or less',
            ];
        }

        return new ValidationResult($errors);
    }
}
