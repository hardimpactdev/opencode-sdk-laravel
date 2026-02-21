<?php

namespace HardImpact\OpenCode\Services;

class TaskRefinementService
{
    private array $requiredSections = [
        'objective',
        'acceptance_criteria',
        'deliverables',
    ];

    public function refine(string $prompt): array
    {
        $decomposedPrompts = $this->decomposePrompt($prompt);

        $specs = [];
        foreach ($decomposedPrompts as $index => $subPrompt) {
            $specs[] = $this->generateSpec($subPrompt, $index, count($decomposedPrompts));
        }

        return $this->establishDependencies($specs);
    }

    private function decomposePrompt(string $prompt): array
    {
        $promptLower = strtolower($prompt);

        // Use regex to split on various conjunctions
        $pattern = '/\s+(?:and|&|\+|as well as|along with|plus|followed by)\s+|\s*,\s*|\s*;\s*/i';
        $parts = preg_split($pattern, $promptLower, -1, PREG_SPLIT_NO_EMPTY);

        $parts = array_filter(array_map('trim', $parts));

        if (count($parts) === 1) {
            return [ucfirst(trim($parts[0]))];
        }

        $tasks = [];
        foreach ($parts as $part) {
            $cleaned = $this->cleanTaskDescription($part);
            if (strlen($cleaned) > 5) {
                $tasks[] = $cleaned;
            }
        }

        return $tasks ?: [$prompt];
    }

    private function cleanTaskDescription(string $description): string
    {
        $description = preg_replace('/^(then |next |after that |finally |first |second |third )/i', '', $description);
        $description = preg_replace('/^(build|create|implement|add|fix|update|refactor|write|set up) /i', '$1 ', $description);

        return ucfirst(trim($description));
    }

    private function generateSpec(string $prompt, int $index, int $total): array
    {
        $spec = [
            'title' => $this->generateTitle($prompt),
            'objective' => $this->generateObjective($prompt),
            'context' => $this->generateContext($prompt, $index, $total),
            'acceptance_criteria' => $this->generateAcceptanceCriteria($prompt),
            'deliverables' => $this->generateDeliverables($prompt),
            'implementation_details' => $this->generateImplementationDetails($prompt),
            'how_to_verify' => $this->generateHowToVerify($prompt),
            'definition_of_done' => $this->generateDefinitionOfDone($prompt),
            'if_blocked' => $this->generateIfBlocked($prompt),
            'blocked_by' => [],
        ];

        return $spec;
    }

    private function establishDependencies(array $specs): array
    {
        $count = count($specs);

        for ($i = 0; $i < $count; $i++) {
            if ($i > 0) {
                $specs[$i]['blocked_by'] = [$i - 1];
            }
        }

        return $specs;
    }

    private function generateTitle(string $prompt): string
    {
        $title = preg_replace('/^(build|create|implement|add|fix|update|refactor|write|set up) /i', '', $prompt);
        $title = substr($title, 0, 100);

        return ucfirst(trim($title)) ?: 'Task';
    }

    private function generateObjective(string $prompt): string
    {
        return "Implement: {$prompt}";
    }

    private function generateContext(string $prompt, int $index, int $total): string
    {
        if ($total === 1) {
            return 'This is a standalone task.';
        }

        if ($index === 0) {
            return "This is the first task in a sequence of {$total} related tasks. It has no dependencies.";
        }

        return 'This is task '.($index + 1)." of {$total}. It depends on the completion of previous tasks.";
    }

    private function generateAcceptanceCriteria(string $prompt): string
    {
        $base = ucfirst($prompt);

        return "- [ ] {$base} is fully implemented\n".
            "- [ ] Code follows project conventions\n".
            "- [ ] All related tests pass\n".
            '- [ ] No regressions introduced';
    }

    private function generateDeliverables(string $prompt): string
    {
        return "- Implementation code\n".
            "- Unit tests\n".
            "- Documentation updates (if applicable)\n".
            '- Verification that requirements are met';
    }

    private function generateImplementationDetails(string $prompt): string
    {
        return 'Follow existing patterns in the codebase. '.
            'Break down the work into small, testable increments. '.
            'Consult project documentation and existing similar implementations.';
    }

    private function generateHowToVerify(string $prompt): string
    {
        return "1. Run the test suite: `php artisan test`\n".
            "2. Check code style: `composer format`\n".
            "3. Run static analysis: `composer analyse`\n".
            '4. Manual verification of the implemented feature';
    }

    private function generateDefinitionOfDone(string $prompt): string
    {
        return "- [ ] Implementation complete\n".
            "- [ ] Tests passing\n".
            "- [ ] Code reviewed (if required)\n".
            "- [ ] Documentation updated\n".
            "- [ ] No TODOs or FIXMEs left\n".
            '- [ ] Changes committed and pushed';
    }

    private function generateIfBlocked(string $prompt): string
    {
        return "If blocked:\n".
            "1. Document the specific blocker\n".
            "2. Comment on the task with details\n".
            "3. Escalate to project lead if blocker persists > 2 hours\n".
            '4. Consider breaking task into smaller pieces';
    }

    public function getRequiredSections(): array
    {
        return $this->requiredSections;
    }
}
