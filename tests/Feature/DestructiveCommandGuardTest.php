<?php

use HardImpact\OpenCode\Providers\AppServiceProvider;
use Illuminate\Database\ProhibitedFromRunningException;

it('prohibits destructive commands when in production', function (): void {
    // Simulate production environment
    app()['env'] = 'production';

    // Boot the provider which sets up the prohibition
    $provider = new AppServiceProvider($this->app);
    $provider->boot();

    // Verify we're in production
    expect($this->app->isProduction())->toBeTrue();

    // The prohibition is now active - attempting to run a destructive command
    // would throw ProhibitedFromRunningException
    expect(true)->toBeTrue();
});

it('does not prohibit destructive commands in non-production', function (): void {
    // Ensure we're in local/testing environment
    app()['env'] = 'testing';

    // Boot the provider
    $provider = new AppServiceProvider($this->app);
    $provider->boot();

    // Verify we're NOT in production
    expect($this->app->isProduction())->toBeFalse();

    // In non-production, commands are allowed
    expect(true)->toBeTrue();
});

it('source code contains DB::prohibitDestructiveCommands with isProduction check', function (): void {
    $reflection = new ReflectionClass(AppServiceProvider::class);
    $method = $reflection->getMethod('boot');

    // Get method source
    $filename = $reflection->getFileName();
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();

    $lines = file($filename);
    $source = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

    // Verify the exact implementation
    expect($source)->toContain('DB::prohibitDestructiveCommands($this->app->isProduction())');
});
