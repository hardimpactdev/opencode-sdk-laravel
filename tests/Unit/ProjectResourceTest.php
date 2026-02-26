<?php

use HardImpact\OpenCode\Data\Project;
use HardImpact\OpenCode\OpenCode;
use HardImpact\OpenCode\Resources\ProjectResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

test('list returns array of projects', function (): void {
    $mockClient = new MockClient([
        MockResponse::make([
            [
                'id' => 'proj_1',
                'worktree' => '/home/user/project-a',
                'vcs' => 'git',
                'name' => 'Project A',
                'icon' => null,
                'commands' => null,
                'time' => ['created' => 1700000000, 'updated' => 1700000001],
                'sandboxes' => [],
            ],
            [
                'id' => 'proj_2',
                'worktree' => '/home/user/project-b',
                'vcs' => null,
                'name' => null,
                'icon' => null,
                'commands' => null,
                'time' => ['created' => 1700000002, 'updated' => 1700000003],
                'sandboxes' => ['/home/user/project-b-worktree'],
            ],
        ]),
    ]);

    $connector = new OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);

    $resource = new ProjectResource($connector);
    $projects = $resource->list();

    expect($projects)->toHaveCount(2);
    expect($projects[0])->toBeInstanceOf(Project::class);
    expect($projects[0]->id)->toBe('proj_1');
    expect($projects[0]->name)->toBe('Project A');
    expect($projects[0]->vcs)->toBe('git');
    expect($projects[0]->sandboxes)->toBe([]);
    expect($projects[1]->id)->toBe('proj_2');
    expect($projects[1]->name)->toBeNull();
    expect($projects[1]->sandboxes)->toBe(['/home/user/project-b-worktree']);
});

test('current returns the active project', function (): void {
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'proj_current',
            'worktree' => '/home/user/active-project',
            'vcs' => 'git',
            'name' => 'Active Project',
            'icon' => ['url' => 'https://example.com/icon.png', 'color' => '#ff0000'],
            'commands' => ['start' => 'npm run dev'],
            'time' => ['created' => 1700000000, 'updated' => 1700000001, 'initialized' => 1700000000],
            'sandboxes' => ['/home/user/active-project-feature'],
        ]),
    ]);

    $connector = new OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);

    $resource = new ProjectResource($connector);
    $project = $resource->current();

    expect($project)->toBeInstanceOf(Project::class);
    expect($project->id)->toBe('proj_current');
    expect($project->name)->toBe('Active Project');
    expect($project->worktree)->toBe('/home/user/active-project');
    expect($project->icon)->toBe(['url' => 'https://example.com/icon.png', 'color' => '#ff0000']);
    expect($project->commands)->toBe(['start' => 'npm run dev']);
    expect($project->sandboxes)->toBe(['/home/user/active-project-feature']);
    expect($project->time)->toHaveKey('initialized');
});

test('update modifies project properties', function (): void {
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'proj_1',
            'worktree' => '/home/user/project',
            'vcs' => 'git',
            'name' => 'Updated Name',
            'icon' => ['override' => 'rocket'],
            'commands' => ['start' => 'php artisan serve'],
            'time' => ['created' => 1700000000, 'updated' => 1700000010],
            'sandboxes' => [],
        ]),
    ]);

    $connector = new OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);

    $resource = new ProjectResource($connector);
    $project = $resource->update(
        id: 'proj_1',
        name: 'Updated Name',
        icon: ['override' => 'rocket'],
        commands: ['start' => 'php artisan serve'],
    );

    expect($project)->toBeInstanceOf(Project::class);
    expect($project->name)->toBe('Updated Name');
    expect($project->icon)->toBe(['override' => 'rocket']);
    expect($project->commands)->toBe(['start' => 'php artisan serve']);
});

test('update can set sandboxes', function (): void {
    $mockClient = new MockClient([
        MockResponse::make([
            'id' => 'proj_1',
            'worktree' => '/home/user/project',
            'vcs' => 'git',
            'name' => null,
            'icon' => null,
            'commands' => null,
            'time' => ['created' => 1700000000, 'updated' => 1700000010],
            'sandboxes' => ['/home/user/project/.worktrees/task-1'],
        ]),
    ]);

    $connector = new OpenCode('http://localhost:3000');
    $connector->withMockClient($mockClient);

    $resource = new ProjectResource($connector);
    $project = $resource->update(
        id: 'proj_1',
        sandboxes: ['/home/user/project/.worktrees/task-1'],
    );

    expect($project)->toBeInstanceOf(Project::class);
    expect($project->sandboxes)->toBe(['/home/user/project/.worktrees/task-1']);
});

test('projects accessor is available on connector', function (): void {
    $connector = new OpenCode('http://localhost:3000');

    expect($connector->projects())->toBeInstanceOf(ProjectResource::class);
});
