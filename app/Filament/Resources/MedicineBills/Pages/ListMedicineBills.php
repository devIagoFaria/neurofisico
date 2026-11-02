<?php

namespace App\Filament\Resources\MedicineBills\Pages;

use App\Filament\Resources\MedicineBills\MedicineBillsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicineBills extends ListRecords
{
    protected static string $resource = MedicineBillsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
