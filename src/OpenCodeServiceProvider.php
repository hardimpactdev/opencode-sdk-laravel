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
            ->hasMigrations([
                '2024_01_01_000001_create_tasks_table',
            ])
            ->hasRoutes(['opencode']);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpenCode::class, function () {
            return new OpenCode(
                baseUrl: config('opencode.base_url'),
            );
        });
    }
}
