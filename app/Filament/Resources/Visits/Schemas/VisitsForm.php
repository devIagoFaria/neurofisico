<?php

namespace App\Filament\Resources\Visits\Schemas;

use Carbon\Carbon;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;

class VisitsForm
{
    public static function configure(Schema $schema): Schema
    {
        $authUser = Auth::user();
        $loggedInDoctorId = null;

        if ($authUser->hasRole('doctor')) {
            $loggedInDoctorId = Doctor::where('user_id', $authUser->id)->value('id');
        }
        $doctors = Doctor::with('user')->get()->pluck('user.full_name', 'id')->toArray();
        $patients = Patient::with('user')->get()->pluck('user.full_name', 'id')->toArray();
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('visit_date')
                                    ->label(__('messages.visit.visit_date') . ':')
                                    ->required()
                                    ->native(false)
                                    ->minDate(function () {
                                        return Carbon::now()->startOfDay()->format('Y-m-d');
                                    })
                                    ->placeholder(__('messages.visit.visit_date')),
                                    Hidden::make('doctor_id')
                                        ->default(fn() => $loggedInDoctorId)
                                        ->visible(fn() => Auth::user()->hasRole('doctor')),
                                    Select::make('doctor_id')
                                        ->label(__('messages.doctor.doctor') . ':')
                                        ->options($doctors)
                                        ->required()
                                        ->searchable()
                                        ->native(false)
                                        ->live()
                                        ->hidden(fn() => Auth::user()->hasRole('doctor')),
                                    Select::make('patient_id')
                                        ->label(__('messages.appointment.patient') . ':')
                                        ->options($patients)
                                        ->required()
                                        ->searchable()
                                        ->native(false)
                                        ->live(),
                                    Textarea::make('description')
                                        ->label(__('messages.visit.description') . ':')
                                        ->rows(5)
                                        ->placeholder(__('messages.visit.description')),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
