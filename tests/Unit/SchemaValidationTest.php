<?php

use HardImpact\OpenCode\Validation\SchemaValidator;

beforeEach(function () {
    $this->validator = new SchemaValidator;
});

test('validate passes for complete spec', function () {
    $spec = [
        'title' => 'Test Task',
        'objective' => 'Build something',
        'acceptance_criteria' => '- [ ] It works',
        'deliverables' => 'Code and tests',
        'context' => 'Some context',
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeTrue();
    expect($result->getErrors())->toBeEmpty();
});

test('validate fails when required sections are missing', function () {
    $spec = [
        'title' => 'Test Task',
        'context' => 'Some context',
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeFalse();
    expect($result->getErrors())->toHaveCount(3);

    $fields = array_column($result->getErrors(), 'field');
    expect($fields)->toContain('objective');
    expect($fields)->toContain('acceptance_criteria');
    expect($fields)->toContain('deliverables');
});

test('validate fails when required sections are empty', function () {
    $spec = [
        'title' => 'Test Task',
        'objective' => '',
        'acceptance_criteria' => '',
        'deliverables' => '',
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeFalse();
    expect($result->getErrors())->toHaveCount(3);
});

test('validate returns specific field paths in errors', function () {
    $spec = [
        'title' => '',
        'objective' => '',
    ];

    $result = $this->validator->validate($spec);
    $errors = $result->getErrors();

    expect($errors[0])->toHaveKey('field');
    expect($errors[0])->toHaveKey('reason');
    expect($errors[0]['field'])->toBe('title');
});

test('validate includes human-readable error messages', function () {
    $spec = [
        'objective' => '',
    ];

    $result = $this->validator->validate($spec);

    expect($result->getErrorMessage())->toContain('objective');
    expect($result->getErrorMessage())->toContain('Required section');
});

test('validate rejects unknown fields', function () {
    $spec = [
        'title' => 'Test',
        'objective' => 'Goal',
        'acceptance_criteria' => 'Criteria',
        'deliverables' => 'Output',
        'unknown_field' => 'value',
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeFalse();
    expect($result->getErrorMessage())->toContain('unknown_field');
});

test('validate checks blocked_by is array', function () {
    $spec = [
        'title' => 'Test',
        'objective' => 'Goal',
        'acceptance_criteria' => 'Criteria',
        'deliverables' => 'Output',
        'blocked_by' => 'not-an-array',
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeFalse();
    expect($result->getErrors()[0]['field'])->toBe('blocked_by');
});

test('validate enforces title length limit', function () {
    $spec = [
        'title' => str_repeat('a', 256),
        'objective' => 'Goal',
        'acceptance_criteria' => 'Criteria',
        'deliverables' => 'Output',
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeFalse();
    expect($result->getErrors()[0]['field'])->toBe('title');
});

test('validate allows valid blocked_by array', function () {
    $spec = [
        'title' => 'Test',
        'objective' => 'Goal',
        'acceptance_criteria' => 'Criteria',
        'deliverables' => 'Output',
        'blocked_by' => [0, 1],
    ];

    $result = $this->validator->validate($spec);

    expect($result->isValid())->toBeTrue();
});
