<?php

namespace JeffersonGoncalves\Filament\TranslationManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JeffersonGoncalves\Filament\TranslationManager\Resources\TranslationLineResource;

class TranslationManagerPlugin implements Plugin
{
    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    public function getId(): string
    {
        return 'filament-translation-manager';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([TranslationLineResource::class]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }
}
