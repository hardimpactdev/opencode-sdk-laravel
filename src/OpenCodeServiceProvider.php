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
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpenCode::class, fn (): \HardImpact\OpenCode\OpenCode => new OpenCode(
            baseUrl: config('opencode.base_url'),
        ));
    }
}
