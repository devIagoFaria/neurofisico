<?php

namespace App\Filament\Resources\DoctorHolidays\Pages;

use App\Filament\Resources\DoctorHolidays\DoctorHolidayResource;
use Filament\Resources\Pages\EditRecord;

class EditDoctorHoliday extends EditRecord
{
    protected static string $resource = DoctorHolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }
}
