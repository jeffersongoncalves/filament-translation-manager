<?php

namespace JeffersonGoncalves\Filament\TranslationManager\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Support\Facades\File;
use JeffersonGoncalves\Filament\TranslationManager\Tests\Fixtures\TestPanelProvider;
use JeffersonGoncalves\Filament\TranslationManager\TranslationManagerServiceProvider;
use JeffersonGoncalves\TranslationManager\TranslationManagerServiceProvider as LaravelTranslationManagerServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    protected string $langPath;

    protected function getPackageProviders($app): array
    {
        // Version-specific providers: Filament 4+ ships the form/schema test helpers in the Schemas provider;
        // Filament 3 views need the @capture directive provider.
        $versionSpecific = array_values(array_filter([
            SchemasServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
        ], 'class_exists'));

        return [
            ...$versionSpecific,
            ActionsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            LaravelTranslationManagerServiceProvider::class,
            TranslationManagerServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // A throwaway lang/ (export writes into it) with one app group in two locales.
        $this->langPath = sys_get_temp_dir().'/filament-translation-manager-'.uniqid();
        File::ensureDirectoryExists($this->langPath.'/en');
        File::ensureDirectoryExists($this->langPath.'/pt_BR');
        File::put($this->langPath.'/en/messages.php', "<?php\n\nreturn ['welcome' => 'Welcome', 'bye' => 'Bye'];\n");
        File::put($this->langPath.'/pt_BR/messages.php', "<?php\n\nreturn ['welcome' => 'Bem-vindo'];\n");
        $app->useLangPath($this->langPath);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('app.locale', 'en');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('translation-manager.namespaces.include', ['filament-translation-manager']);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(dirname((string) (new ReflectionClass(LaravelTranslationManagerServiceProvider::class))->getFileName()).'/../database/migrations');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->langPath);

        parent::tearDown();
    }
}
