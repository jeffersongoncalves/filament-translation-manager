## Filament Translation Manager

Filament resource for jeffersongoncalves/laravel-translation-manager: edit app, JSON and vendor package translations from the panel, filter missing keys per locale and export the overrides to `lang/`.

### Installation

@verbatim
<code-snippet name="Install the plugin" lang="bash">
composer require jeffersongoncalves/filament-translation-manager:"^2.0"
php artisan migrate
php artisan translation-manager:scan
</code-snippet>
@endverbatim

### Register Plugin

@verbatim
<code-snippet name="Register in PanelProvider" lang="php">
use JeffersonGoncalves\Filament\TranslationManager\TranslationManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            TranslationManagerPlugin::make()
                // ->navigationGroup('Settings')
                // ->navigationSort(10)
                ,
        ]);
}
</code-snippet>
@endverbatim

### Architecture
- `TranslationManagerPlugin` registers `TranslationLineResource` (single `ManageTranslationLines` page, edit in a modal)
- Model: `JeffersonGoncalves\TranslationManager\Models\TranslationLine` — `source` = file values, `text` = overrides, per locale
- Scan/export go through `JeffersonGoncalves\TranslationManager\TranslationManager`
- Translations live under `filament-translation-manager::translation-manager.*`

### Best Practices
- Restrict access with a policy for `TranslationLine`
- Exclude package namespaces nobody edits (`translation-manager.namespaces.exclude`) to keep the table small
- Export with "remove from database" once overrides are committed to `lang/`
