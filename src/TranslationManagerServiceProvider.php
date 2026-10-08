<?php

namespace JeffersonGoncalves\Filament\TranslationManager;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TranslationManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-translation-manager')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
