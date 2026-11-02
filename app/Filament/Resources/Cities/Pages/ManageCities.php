<?php

namespace App\Filament\Resources\Cities\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Cities\CitiesResource;

class ManageCities extends ManageRecords
{
    protected static string $resource = CitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->modalWidth(Width::Medium)
                ->successNotificationTitle(__('messages.flash.city_create')),
        ];
    }
}
