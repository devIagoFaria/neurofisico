<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use Filament\Resources\Pages\EditRecord;
use libphonenumber\PhoneNumberUtil;
class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;

        $doctor = $record->doctor;
        if ($doctor) {
            if (isset($data['specializations'])) {
                $doctor->specializations()->sync($data['specializations']);
            }
        }
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

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.doctor_update');
    }
}
