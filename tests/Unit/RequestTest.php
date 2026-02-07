<?php

use HardImpact\OpenCode\Requests\Sessions\CreateSession;
use HardImpact\OpenCode\Requests\Sessions\DeleteSession;
use HardImpact\OpenCode\Requests\Sessions\GetMessages;
use HardImpact\OpenCode\Requests\Sessions\GetSession;
use HardImpact\OpenCode\Requests\Sessions\ListSessions;
use HardImpact\OpenCode\Requests\Sessions\SendMessage;
use HardImpact\OpenCode\Requests\Sessions\SendMessageAsync;
use HardImpact\OpenCode\Requests\Sessions\UpdateSession;
use HardImpact\OpenCode\Requests\Sessions\RunCommand;
use HardImpact\OpenCode\Requests\Questions\AnswerQuestion;
use HardImpact\OpenCode\Requests\Questions\RejectQuestion;
use HardImpact\OpenCode\Requests\Providers\GetProviders;
use Saloon\Enums\Method;

it('builds CreateSession request correctly', function () {
    $request = new CreateSession('/path/to/project', 'My Session');

    expect($request->resolveEndpoint())->toBe('/session');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds GetSession request correctly', function () {
    $request = new GetSession('ses_123');

    expect($request->resolveEndpoint())->toBe('/session/ses_123');
    expect($request->getMethod())->toBe(Method::GET);
});

it('builds ListSessions request correctly', function () {
    $request = new ListSessions;

    expect($request->resolveEndpoint())->toBe('/session');
    expect($request->getMethod())->toBe(Method::GET);
});

it('builds DeleteSession request correctly', function () {
    $request = new DeleteSession('ses_123');

    expect($request->resolveEndpoint())->toBe('/session/ses_123');
    expect($request->getMethod())->toBe(Method::DELETE);
});

it('builds SendMessage request correctly', function () {
    $request = new SendMessage(
        id: 'ses_123',
        providerID: 'anthropic',
        modelID: 'claude-sonnet-4-20250514',
        parts: [['type' => 'text', 'text' => 'Hello']],
    );

    expect($request->resolveEndpoint())->toBe('/session/ses_123/message');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds SendMessageAsync request correctly', function () {
    $request = new SendMessageAsync(
        id: 'ses_123',
        providerID: 'anthropic',
        modelID: 'claude-sonnet-4-20250514',
        parts: [['type' => 'text', 'text' => 'Hello']],
    );

    expect($request->resolveEndpoint())->toBe('/session/ses_123/prompt_async');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds GetMessages request correctly', function () {
    $request = new GetMessages('ses_123');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/message');
    expect($request->getMethod())->toBe(Method::GET);
});

it('builds AnswerQuestion request correctly', function () {
    $request = new AnswerQuestion('ses_123', 'perm_456', 'always');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/permissions/perm_456');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds RejectQuestion request correctly', function () {
    $request = new RejectQuestion('ses_123', 'perm_456');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/permissions/perm_456');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds UpdateSession request correctly', function () {
    $request = new UpdateSession('ses_123', 'New Title');

    expect($request->resolveEndpoint())->toBe('/session/ses_123');
    expect($request->getMethod())->toBe(Method::PATCH);
});

it('builds RunCommand request correctly', function () {
    $request = new RunCommand('ses_123', 'compact', 'some args');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/command');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds GetProviders request correctly', function () {
    $request = new GetProviders;

    expect($request->resolveEndpoint())->toBe('/config/providers');
    expect($request->getMethod())->toBe(Method::GET);
});
