<?php

namespace JeffersonGoncalves\Filament\TranslationManager\Resources;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use JeffersonGoncalves\Filament\TranslationManager\Resources\TranslationLineResource\Pages\ManageTranslationLines;
use JeffersonGoncalves\Filament\TranslationManager\TranslationManagerPlugin;
use JeffersonGoncalves\TranslationManager\Models\TranslationLine;
use JeffersonGoncalves\TranslationManager\TranslationManager;

class TranslationLineResource extends Resource
{
    protected static ?string $model = TranslationLine::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    public static function getNavigationGroup(): ?string
    {
        return TranslationManagerPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return TranslationManagerPlugin::get()->getNavigationSort();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-translation-manager::translation-manager.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament-translation-manager::translation-manager.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-translation-manager::translation-manager.plural_model_label');
    }

    /**
     * @return list<string>
     */
    public static function locales(): array
    {
        return app(TranslationManager::class)->locales();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(array_map(
                fn (string $locale) => Textarea::make("text.{$locale}")
                    ->label($locale)
                    ->rows(2)
                    ->autosize()
                    ->placeholder(fn (?TranslationLine $record) => $record?->source[$locale] ?? __('filament-translation-manager::translation-manager.missing')),
                static::locales(),
            ));
    }

    public static function table(Table $table): Table
    {
        $locales = static::locales();
        $visible = array_unique([app()->getLocale(), (string) config('app.fallback_locale')]);

        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('namespace')
                    ->label(__('filament-translation-manager::translation-manager.namespace'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === '*' ? 'app' : $state)
                    ->sortable(),
                TextColumn::make('group')
                    ->label(__('filament-translation-manager::translation-manager.group'))
                    ->formatStateUsing(fn (string $state) => $state === '*' ? 'json' : $state)
                    ->sortable(),
                TextColumn::make('key')
                    ->label(__('filament-translation-manager::translation-manager.key'))
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn (Builder $q) => $q
                        ->where('key', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%")
                        ->orWhere('text', 'like', "%{$search}%")))
                    ->wrap()
                    ->sortable(),
                ...array_map(fn (string $locale) => TextColumn::make("locale_{$locale}")
                    ->label($locale)
                    ->state(fn (TranslationLine $record) => $record->value($locale))
                    ->color(fn (TranslationLine $record) => filled($record->text[$locale] ?? null) ? 'primary' : null)
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: ! in_array($locale, $visible, true)), $locales),
            ])
            ->filters([
                SelectFilter::make('namespace')
                    ->label(__('filament-translation-manager::translation-manager.namespace'))
                    ->options(fn () => TranslationLine::query()->distinct()->orderBy('namespace')->pluck('namespace', 'namespace')
                        ->map(fn (string $namespace) => $namespace === '*' ? 'app' : $namespace)->all())
                    ->searchable(),
                Filter::make('missing')
                    ->schema([
                        Select::make('locale')
                            ->label(__('filament-translation-manager::translation-manager.missing_in'))
                            ->options(array_combine($locales, $locales)),
                    ])
                    ->query(fn (Builder $query, array $data) => static::whereMissing($query, $data['locale'] ?? null))
                    ->indicateUsing(fn (array $data) => filled($data['locale'] ?? null)
                        ? __('filament-translation-manager::translation-manager.missing_in').': '.$data['locale'] : null),
                TernaryFilter::make('overridden')
                    ->label(__('filament-translation-manager::translation-manager.overridden'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('text'),
                        false: fn (Builder $query) => $query->whereNull('text'),
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(fn (TranslationLine $record) => $record->fullKey()),
                Action::make('revert')
                    ->label(__('filament-translation-manager::translation-manager.revert'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (TranslationLine $record) => $record->text !== null)
                    ->action(fn (TranslationLine $record) => $record->update(['text' => null])),
            ]);
    }

    /**
     * @param  Builder<TranslationLine>  $query
     * @return Builder<TranslationLine>
     */
    protected static function whereMissing(Builder $query, ?string $locale): Builder
    {
        return filled($locale) ? $query->missing($locale) : $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTranslationLines::route('/'),
        ];
    }
}
