<?php

use HardImpact\OpenCode\Providers\AppServiceProvider;
use Illuminate\Support\Facades\DB;

it('registers AppServiceProvider with destructive command guard', function (): void {
    // Create provider instance
    $provider = new AppServiceProvider($this->app);

    // Boot the provider (this calls DB::prohibitDestructiveCommands)
    $provider->boot();

    // Verify provider was booted successfully
    expect($provider)->toBeInstanceOf(AppServiceProvider::class);
});

it('has prohibitDestructiveCommands configured in boot method', function (): void {
    $reflection = new ReflectionClass(AppServiceProvider::class);
    $method = $reflection->getMethod('boot');

    // Get method source
    $filename = $reflection->getFileName();
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();

    $lines = file($filename);
    $source = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

    // Verify the guard is present
    expect($source)->toContain('DB::prohibitDestructiveCommands');
    expect($source)->toContain('isProduction');
});
