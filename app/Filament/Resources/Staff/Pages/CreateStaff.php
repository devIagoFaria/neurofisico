<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use libphonenumber\PhoneNumberUtil;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        $data['type'] = User::STAFF;


        if (! empty($data['contact'])) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phone = $phoneUtil->parse($data['contact']);

            $data['region_code'] = (string) $phone->getCountryCode();
            $data['contact'] = (string) $phone->getNationalNumber();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $data = $this->data;

        $role = $record->assignRole($data['role_id']);
        $role->givePermissionTo('manage_admin_dashboard');

        $record->sendEmailVerificationNotification();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.staff_create');
    }
}
