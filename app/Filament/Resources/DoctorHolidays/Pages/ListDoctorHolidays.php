<?php

namespace App\Filament\Resources\DoctorHolidays\Pages;

use App\Filament\Resources\DoctorHolidays\DoctorHolidayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDoctorHolidays extends ListRecords
{
    protected static string $resource = DoctorHolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('messages.holiday.add_holiday')),
        ];
    }
}
