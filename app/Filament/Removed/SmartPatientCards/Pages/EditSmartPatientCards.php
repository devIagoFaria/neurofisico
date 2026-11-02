<?php

namespace App\Filament\Doctor\Resources\SmartPatientCards\Pages;

use App\Filament\Doctor\Resources\SmartPatientCards\SmartPatientCardsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSmartPatientCards extends EditRecord
{
    protected static string $resource = SmartPatientCardsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index'))
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.smart_patient_card.template_update');
    }
}
