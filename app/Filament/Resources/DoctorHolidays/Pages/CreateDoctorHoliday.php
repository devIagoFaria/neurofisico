<?php

namespace App\Filament\Resources\DoctorHolidays\Pages;

use App\Filament\Resources\DoctorHolidays\DoctorHolidayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctorHoliday extends CreateRecord
{
    protected static string $resource = DoctorHolidayResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.doctor_holiday');
    }
}
