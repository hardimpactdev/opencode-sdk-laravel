<?php

use HardImpact\OpenCode\Requests\Providers\GetProviders;
use HardImpact\OpenCode\Requests\Questions\AnswerQuestion;
use HardImpact\OpenCode\Requests\Questions\RejectQuestion;
use HardImpact\OpenCode\Requests\Sessions\CreateSession;
use HardImpact\OpenCode\Requests\Sessions\DeleteSession;
use HardImpact\OpenCode\Requests\Sessions\GetMessages;
use HardImpact\OpenCode\Requests\Sessions\GetSession;
use HardImpact\OpenCode\Requests\Sessions\ListSessions;
use HardImpact\OpenCode\Requests\Sessions\RunCommand;
use HardImpact\OpenCode\Requests\Sessions\SendMessage;
use HardImpact\OpenCode\Requests\Sessions\SendMessageAsync;
use HardImpact\OpenCode\Requests\Sessions\UpdateSession;
use Saloon\Enums\Method;

it('builds CreateSession request correctly', function (): void {
    $request = new CreateSession('/path/to/project', 'My Session');

    expect($request->resolveEndpoint())->toBe('/session');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds GetSession request correctly', function (): void {
    $request = new GetSession('ses_123');

    expect($request->resolveEndpoint())->toBe('/session/ses_123');
    expect($request->getMethod())->toBe(Method::GET);
});

it('builds ListSessions request correctly', function (): void {
    $request = new ListSessions;

    expect($request->resolveEndpoint())->toBe('/session');
    expect($request->getMethod())->toBe(Method::GET);
});

it('builds DeleteSession request correctly', function (): void {
    $request = new DeleteSession('ses_123');

    expect($request->resolveEndpoint())->toBe('/session/ses_123');
    expect($request->getMethod())->toBe(Method::DELETE);
});

it('builds SendMessage request correctly', function (): void {
    $request = new SendMessage(
        id: 'ses_123',
        providerID: 'anthropic',
        modelID: 'claude-sonnet-4-20250514',
        parts: [['type' => 'text', 'text' => 'Hello']],
    );

    expect($request->resolveEndpoint())->toBe('/session/ses_123/message');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds SendMessageAsync request correctly', function (): void {
    $request = new SendMessageAsync(
        id: 'ses_123',
        providerID: 'anthropic',
        modelID: 'claude-sonnet-4-20250514',
        parts: [['type' => 'text', 'text' => 'Hello']],
    );

    expect($request->resolveEndpoint())->toBe('/session/ses_123/prompt_async');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds GetMessages request correctly', function (): void {
    $request = new GetMessages('ses_123');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/message');
    expect($request->getMethod())->toBe(Method::GET);
});

it('builds GetMessages request with directory parameter', function (): void {
    $request = new GetMessages('ses_123', '/path/to/project');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/message');
    expect($request->getMethod())->toBe(Method::GET);
    expect($request->query()->get('directory'))->toBe('/path/to/project');
});

it('builds AnswerQuestion request correctly', function (): void {
    $request = new AnswerQuestion('ses_123', 'perm_456', 'always');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/permissions/perm_456');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds RejectQuestion request correctly', function (): void {
    $request = new RejectQuestion('ses_123', 'perm_456');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/permissions/perm_456');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds UpdateSession request correctly', function (): void {
    $request = new UpdateSession('ses_123', 'New Title');

    expect($request->resolveEndpoint())->toBe('/session/ses_123');
    expect($request->getMethod())->toBe(Method::PATCH);
});

it('builds RunCommand request correctly', function (): void {
    $request = new RunCommand('ses_123', 'compact', 'some args');

    expect($request->resolveEndpoint())->toBe('/session/ses_123/command');
    expect($request->getMethod())->toBe(Method::POST);
});

it('builds GetProviders request correctly', function (): void {
    $request = new GetProviders;

    expect($request->resolveEndpoint())->toBe('/config/providers');
    expect($request->getMethod())->toBe(Method::GET);
});
