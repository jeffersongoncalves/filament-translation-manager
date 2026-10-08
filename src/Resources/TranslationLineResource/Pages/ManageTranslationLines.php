<?php

namespace JeffersonGoncalves\Filament\TranslationManager\Resources\TranslationLineResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use JeffersonGoncalves\Filament\TranslationManager\Resources\TranslationLineResource;
use JeffersonGoncalves\TranslationManager\TranslationManager;

class ManageTranslationLines extends ManageRecords
{
    protected static string $resource = TranslationLineResource::class;

    protected function getHeaderActions(): array
    {
        $locales = TranslationLineResource::locales();

        return [
            Action::make('scan')
                ->label(__('filament-translation-manager::translation-manager.scan'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(function (TranslationManager $manager) {
                    $count = $manager->scan();

                    Notification::make()
                        ->success()
                        ->title(__('filament-translation-manager::translation-manager.scanned', ['count' => $count]))
                        ->send();
                }),
            Action::make('export')
                ->label(__('filament-translation-manager::translation-manager.export'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->schema([
                    CheckboxList::make('locales')
                        ->label(__('filament-translation-manager::translation-manager.locales'))
                        ->helperText(__('filament-translation-manager::translation-manager.locales_help'))
                        ->options(array_combine($locales, $locales))
                        ->columns(4),
                    Toggle::make('clear')
                        ->label(__('filament-translation-manager::translation-manager.clear'))
                        ->helperText(__('filament-translation-manager::translation-manager.clear_help')),
                ])
                ->action(function (array $data, TranslationManager $manager) {
                    $files = $manager->export(array_values($data['locales'] ?? []), (bool) ($data['clear'] ?? false));

                    Notification::make()
                        ->success()
                        ->title(__('filament-translation-manager::translation-manager.exported', ['count' => count($files)]))
                        ->send();
                }),
        ];
    }
}
