<?php

use HardImpact\OpenCode\OpenCode;
use HardImpact\OpenCode\Resources\EventResource;
use HardImpact\OpenCode\Resources\ProviderResource;
use HardImpact\OpenCode\Resources\QuestionResource;
use HardImpact\OpenCode\Resources\SessionResource;

it('resolves default base url', function () {
    $connector = new OpenCode;

    expect($connector->resolveBaseUrl())->toBe('http://localhost:4096');
});

it('resolves custom base url', function () {
    $connector = new OpenCode('http://localhost:9999');

    expect($connector->resolveBaseUrl())->toBe('http://localhost:9999');
});

it('provides resource accessors', function () {
    $connector = new OpenCode;

    expect($connector->sessions())->toBeInstanceOf(SessionResource::class);
    expect($connector->events())->toBeInstanceOf(EventResource::class);
    expect($connector->questions())->toBeInstanceOf(QuestionResource::class);
    expect($connector->providers())->toBeInstanceOf(ProviderResource::class);
});
