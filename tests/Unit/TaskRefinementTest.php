<?php

use HardImpact\OpenCode\Services\TaskRefinementService;

beforeEach(function () {
    $this->service = new TaskRefinementService;
});

test('refine converts single prompt into single task spec', function () {
    $prompt = 'Build a user authentication system';

    $specs = $this->service->refine($prompt);

    expect($specs)->toHaveCount(1);
    expect($specs[0])->toHaveKeys([
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
    ]);
});

test('refine splits multi-objective prompts into separate tasks', function () {
    $prompt = 'Build a user authentication system and create an admin dashboard';

    $specs = $this->service->refine($prompt);

    expect($specs)->toHaveCount(2);
    expect($specs[0]['title'])->not->toBe($specs[1]['title']);
});

test('refine establishes blocked_by dependencies for multi-task decomposition', function () {
    $prompt = 'Build A and then implement B';

    $specs = $this->service->refine($prompt);

    expect($specs)->toHaveCount(2);
    expect($specs[0]['blocked_by'])->toBe([]);
    expect($specs[1]['blocked_by'])->toBe([0]);
});

test('refine handles complex multi-part prompts with multiple conjunctions', function () {
    $prompt = 'Create login form, add password reset, and implement two-factor authentication';

    $specs = $this->service->refine($prompt);

    expect($specs)->toHaveCount(3);

    for ($i = 0; $i < 3; $i++) {
        expect($specs[$i])->toHaveKey('title');
        expect($specs[$i])->toHaveKey('objective');
        expect($specs[$i])->toHaveKey('acceptance_criteria');
        expect($specs[$i])->toHaveKey('blocked_by');
    }

    expect($specs[0]['blocked_by'])->toBe([]);
    expect($specs[1]['blocked_by'])->toBe([0]);
    expect($specs[2]['blocked_by'])->toBe([1]);
});

test('refine populates all required sections', function () {
    $prompt = 'Implement feature X';

    $specs = $this->service->refine($prompt);
    $spec = $specs[0];

    expect($spec['title'])->not->toBeEmpty();
    expect($spec['objective'])->not->toBeEmpty();
    expect($spec['context'])->not->toBeEmpty();
    expect($spec['acceptance_criteria'])->not->toBeEmpty();
    expect($spec['deliverables'])->not->toBeEmpty();
    expect($spec['implementation_details'])->not->toBeEmpty();
    expect($spec['how_to_verify'])->not->toBeEmpty();
    expect($spec['definition_of_done'])->not->toBeEmpty();
    expect($spec['if_blocked'])->not->toBeEmpty();
});

test('refine generates contextual descriptions based on task position', function () {
    $prompt = 'Build authentication system and create admin dashboard';

    $specs = $this->service->refine($prompt);

    expect($specs)->toHaveCount(2);
    expect($specs[0]['context'])->toContain('first');
    expect($specs[1]['context'])->toContain('2');
});

test('refine cleans up task titles', function () {
    $prompt = 'build an amazing feature';

    $specs = $this->service->refine($prompt);

    expect($specs[0]['title'])->toBe('An amazing feature');
});

test('required sections constant contains expected fields', function () {
    $required = $this->service->getRequiredSections();

    expect($required)->toContain('objective');
    expect($required)->toContain('acceptance_criteria');
    expect($required)->toContain('deliverables');
});
