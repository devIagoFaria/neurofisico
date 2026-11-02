<?php

namespace App\Filament\Doctor\Resources\Holidays\Schemas;

use App\Models\Doctor;
use App\Models\DoctorHoliday;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        $authUser = Auth::user();
        $doctorId = Doctor::where('user_id', $authUser->id)->value('id');

        return $schema
            ->components([
                Section::make([
                    Hidden::make('doctor_id')
                        ->default($doctorId),
                    DatePicker::make('date')
                        ->date(datePickerFormat())
                        ->label(__('messages.appointment.date') . ':')
                        ->placeholder(__('messages.appointment.date'))
                        ->minDate(today()->addDay())
                        ->native(false)
                        ->required()
                        ->rules([
                            function () use ($doctorId) {
                                return function (string $attribute, $value, $fail) use ($doctorId) {
                                    $exists = DoctorHoliday::where('doctor_id', $doctorId)
                                        ->whereDate('date', $value)
                                        ->exists();

                                    if ($exists) {
                                        $fail(__('messages.flash.holiday_already_is_exist'));
                                    }
                                };
                            },
                        ]),
                    TextInput::make('name')
                        ->label(__('messages.web.reason') . ':')
                        ->placeholder(__('messages.web.reason')),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
