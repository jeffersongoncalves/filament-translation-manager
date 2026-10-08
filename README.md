<div class="filament-hidden">

![Filament Translation Manager](https://raw.githubusercontent.com/jeffersongoncalves/filament-translation-manager/2.x/art/jeffersongoncalves-filament-translation-manager.png)

</div>

# Filament Translation Manager

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-translation-manager.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-translation-manager)
[![Tests](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-translation-manager/tests.yml?branch=2.x&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/filament-translation-manager/actions?query=workflow%3ATests+branch%3A2.x)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-translation-manager.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-translation-manager)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-translation-manager.svg?style=flat-square)](LICENSE.md)

Edit your application's translations from the Filament panel — **including the lines of every installed package** (Filament itself, your plugins, `validation`…) — find the keys missing in each locale, and export the result back to `lang/` so it lives in Git.

Built on [jeffersongoncalves/laravel-translation-manager](https://github.com/jeffersongoncalves/laravel-translation-manager): the lang files stay the source of truth and the panel only stores overrides, laid over the files at runtime.

## Why another translation manager

- **Vendor translations, not only yours.** Package namespaces (`filament-panels::`, `filament-actions::`, any plugin) are scanned and editable, nested groups (`pages/dashboard`) included. Exporting writes them to `lang/vendor/{package}/…`, the files Laravel already lays over the package's own.
- **Files stay the source of truth.** No import step that copies everything into the database: unedited keys keep coming from the files, so a `composer update` that changes a package's wording is picked up.
- **Missing keys per locale**, from the table filter.
- **Filament 3, 4 and 5**, one branch each.

## Compatibility

| Branch | Filament | Package version |
|--------|----------|-----------------|
| 1.x | 3.x | `^1.0` |
| 2.x | 4.x | `^2.0` |
| 3.x | 5.x | `^3.0` |

## Installation

```bash
composer require jeffersongoncalves/filament-translation-manager:"^2.0"
php artisan migrate
php artisan translation-manager:scan
```

## Usage

Add the plugin to your panel provider:

```php
use JeffersonGoncalves\Filament\TranslationManager\TranslationManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            TranslationManagerPlugin::make(),
        ]);
}
```

Then open **Translations**:

| Feature | |
|---------|---|
| Table | One row per key: namespace (`app` for your own lines), group (`json` for `lang/{locale}.json`), key and the value per locale. Overridden values are highlighted. Locale columns other than the current and fallback locale are hidden by default — toggle them on. |
| Search | Matches keys, file values and overrides. |
| Filters | Namespace, **missing in** a locale, overridden or not. |
| Edit | A field per locale; the placeholder shows the file value. Leave a field empty to keep the file value. |
| Revert to file | Drops the overrides of a line. |
| Scan lang files | Re-reads the files (after adding keys or installing packages). Overrides are never touched. |
| Export to lang files | Writes the overrides into `lang/` (all or some locales), optionally removing them from the database afterwards. |

### Customization

```php
TranslationManagerPlugin::make()
    ->navigationGroup('Settings')
    ->navigationSort(10),
```

Locales, scanned namespaces, table name and cache store are configured in laravel-translation-manager — see its [configuration](https://github.com/jeffersongoncalves/laravel-translation-manager#configuration). Excluding the big Filament namespaces you don't plan to edit keeps the table small:

```php
// config/translation-manager.php
'namespaces' => [
    'include' => [],
    'exclude' => ['filament-forms', 'filament-tables', 'filament-query-builder'],
],
```

### Authorization

The resource follows Filament's usual rules: register a policy for `JeffersonGoncalves\TranslationManager\Models\TranslationLine` to decide who may view and edit translations.

## Requirements

- PHP 8.2 or higher
- Filament 4.x

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
