<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use Filament\Resources\Pages\CreateRecord;
use libphonenumber\PhoneNumberUtil;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $data = $this->data;

        $record->assignRole('doctor');

        $doctor = $record->doctor;

        if (isset($data['specializations']) && ! empty($data['specializations'])) {
            $doctor->specializations()->attach($data['specializations']);
        }
        $record->sendEmailVerificationNotification();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['contact'])) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phone = $phoneUtil->parse($data['contact']);

            $data['region_code'] = (string) $phone->getCountryCode();
            $data['contact'] = (string) $phone->getNationalNumber();
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.doctor_create');
    }
}
