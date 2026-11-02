<?php

namespace App\Filament\Resources\Countries\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Countries\CountriesResource;

class ManageCountries extends ManageRecords
{
    protected static string $resource = CountriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->modalWidth(Width::Medium)
                ->successNotificationTitle(__('messages.flash.country_create')),
        ];
    }
}
