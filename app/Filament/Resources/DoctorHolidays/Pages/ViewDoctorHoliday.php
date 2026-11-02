<?php

namespace App\Filament\Resources\DoctorHolidays\Pages;

use App\Filament\Resources\DoctorHolidays\DoctorHolidayResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDoctorHoliday extends ViewRecord
{
    protected static string $resource = DoctorHolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            backActionResource(static::$resource::getUrl('index')),
        ];
    }
}
