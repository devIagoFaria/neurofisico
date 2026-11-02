<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use libphonenumber\PhoneNumberUtil;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        
        if (! empty($data['contact'])) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phone = $phoneUtil->parse($data['contact']);

            $data['region_code'] = (string) $phone->getCountryCode();
            $data['contact'] = (string) $phone->getNationalNumber();
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;

        if (isset($data['role_id']) && ! empty($data['role_id'])) {
            $record->syncRoles($data['role_id']);
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.staff_update');
    }
}
