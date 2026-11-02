<?php

namespace App\Filament\Resources\MedicineBrands\Pages;

use App\Filament\Resources\MedicineBrands\MedicineBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicineBrands extends ListRecords
{
    protected static string $resource = MedicineBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
