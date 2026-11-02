<?php

namespace App\Filament\Doctor\Resources\Holidays\Pages;

use App\Filament\Doctor\Resources\Holidays\HolidayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('messages.holiday.add_holiday')),
        ];
    }
}
