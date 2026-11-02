<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Resources\Pages\CreateRecord;
use libphonenumber\PhoneNumberUtil;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }
    protected function afterCreate(): void
    {
        $this->record->assignRole('patient');

        $this->record->sendEmailVerificationNotification();
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
        return __('messages.flash.patient_create');
    }
}
