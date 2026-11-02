<?php

namespace App\Filament\Resources\SmartPatientCards\Pages;

use App\Filament\Resources\SmartPatientCards\SmartPatientCardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSmartPatientCard extends EditRecord
{
    protected static string $resource = SmartPatientCardResource::class;

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
