<?php

namespace App\Filament\Resources\Specializations\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Specializations\SpecializationsResource;

class ManageSpecializations extends ManageRecords
{
    protected static string $resource = SpecializationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Medium)
                ->createAnother(false)
                ->successNotificationTitle(__('messages.flash.specialization_create')),
        ];
    }
}
