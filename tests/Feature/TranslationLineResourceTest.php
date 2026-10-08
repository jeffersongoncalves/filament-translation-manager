<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\File;
use JeffersonGoncalves\Filament\TranslationManager\Resources\TranslationLineResource;
use JeffersonGoncalves\Filament\TranslationManager\Resources\TranslationLineResource\Pages\ManageTranslationLines;
use JeffersonGoncalves\TranslationManager\Models\TranslationLine;
use JeffersonGoncalves\TranslationManager\TranslationManager;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('test'));
    $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com']));
    app(TranslationManager::class)->scan();
});

function messageLine(string $key): TranslationLine
{
    return TranslationLine::query()->where(['namespace' => '*', 'group' => 'messages', 'key' => $key])->firstOrFail();
}

it('registers the resource on the panel with the configured group', function () {
    expect(Filament::getPanel('test')->getResources())->toContain(TranslationLineResource::class)
        ->and(TranslationLineResource::getNavigationGroup())->toBe('Settings')
        ->and(TranslationLineResource::getNavigationLabel())->toBe('Translations');
});

it('lists the scanned lines, including the vendor ones', function () {
    $vendor = TranslationLine::query()->where('namespace', 'filament-translation-manager')->firstOrFail();

    Livewire::test(ManageTranslationLines::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([messageLine('welcome'), $vendor]);
});

it('filters the lines missing in a locale', function () {
    Livewire::test(ManageTranslationLines::class)
        ->filterTable('namespace', '*')
        ->filterTable('missing', ['locale' => 'pt_BR'])
        ->assertCanSeeTableRecords([messageLine('bye')])
        ->assertCanNotSeeTableRecords([messageLine('welcome')]);
});

it('overrides a line from the edit modal', function () {
    $line = messageLine('bye');

    Livewire::test(ManageTranslationLines::class)
        ->callTableAction('edit', $line, data: ['text' => ['en' => '', 'pt_BR' => 'Tchau']])
        ->assertHasNoTableActionErrors();

    app()->forgetInstance('translator');
    app()->setLocale('pt_BR');

    expect($line->refresh()->text)->toBe(['pt_BR' => 'Tchau'])
        ->and(__('messages.bye'))->toBe('Tchau');
});

it('reverts an override', function () {
    $line = messageLine('welcome');
    $line->update(['text' => ['pt_BR' => 'Olá']]);

    Livewire::test(ManageTranslationLines::class)
        ->callTableAction('revert', $line);

    expect($line->refresh()->text)->toBeNull();
});

it('exports the overrides to the lang files', function () {
    messageLine('bye')->update(['text' => ['pt_BR' => 'Tchau']]);

    Livewire::test(ManageTranslationLines::class)
        ->callAction('export', data: ['locales' => ['pt_BR'], 'clear' => true])
        ->assertNotified();

    expect(File::getRequire($this->langPath.'/pt_BR/messages.php'))->toBe(['welcome' => 'Bem-vindo', 'bye' => 'Tchau'])
        ->and(messageLine('bye')->text)->toBeNull();
});

it('rescans from the header action', function () {
    TranslationLine::query()->delete();

    Livewire::test(ManageTranslationLines::class)
        ->callAction('scan')
        ->assertNotified();

    expect(messageLine('welcome')->source)->toBe(['en' => 'Welcome', 'pt_BR' => 'Bem-vindo']);
});

it('uses translated labels', function () {
    app()->setLocale('pt_BR');

    expect(TranslationLineResource::getNavigationLabel())->toBe('Traduções');
});
