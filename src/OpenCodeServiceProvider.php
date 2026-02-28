<?php

namespace HardImpact\OpenCode;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OpenCodeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('opencode')
            ->hasConfigFile()
            ->hasMigration('create_open_code_sessions_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpenCode::class, fn (): \HardImpact\OpenCode\OpenCode => new OpenCode(
            baseUrl: config('opencode.base_url'),
        ));

        $this->app->singleton(SessionManager::class);
    }
}
