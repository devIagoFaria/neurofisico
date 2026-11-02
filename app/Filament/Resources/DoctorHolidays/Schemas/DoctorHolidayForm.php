<?php

namespace App\Filament\Resources\DoctorHolidays\Schemas;

use App\Models\Doctor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorHolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('doctor_id')
                        ->options(
                            Doctor::with('user')
                                ->get()
                                ->pluck('user.full_name', 'id')
                        )
                        ->label(__('messages.doctor.doctor').':')
                        ->placeholder(__('messages.doctor.doctor'))
                        ->native(false)
                        ->required(),
                    DatePicker::make('date')
                        ->date(datePickerFormat())
                        ->label(__('messages.appointment.date').':')
                        ->placeholder(__('messages.appointment.date'))
                        ->minDate(today()->addDay())
                        ->default(today()->addDay())
                        ->native(false)
                        ->required(),
                    TextInput::make('name')
                        ->label(__('messages.web.reason').':')
                        ->placeholder(__('messages.web.reason'))
                        ->required(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
