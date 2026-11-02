<?php

namespace App\Filament\Doctor\Resources\Holidays\Pages;

use App\Filament\Doctor\Resources\Holidays\HolidayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHoliday extends CreateRecord
{
    protected static string $resource = HolidayResource::class;

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
