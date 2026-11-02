<?php

namespace App\Filament\Resources\States\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\States\StatesResource;

class ManageStates extends ManageRecords
{
    protected static string $resource = StatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->modalWidth(Width::Medium)
                ->successNotificationTitle(__('messages.flash.state_create')),
        ];
    }
}
