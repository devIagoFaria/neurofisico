<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Models\City;
use App\Models\Country;
use App\Models\Patient;
use App\Models\State;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Forms\Components\PhoneInput;
use Illuminate\Support\HtmlString;
class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Hidden::make('type')->default(User::PATIENT),
                                TextInput::make('first_name')
                                    ->label(__('messages.patient.first_name') . ':')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('messages.patient.first_name')),

                                TextInput::make('last_name')
                                    ->label(__('messages.patient.last_name') . ':')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('messages.patient.last_name')),

                                Group::make([
                                    TextInput::make('patient_unique_id')
                                        ->label(__('messages.patient.patient_unique_id') . ':')
                                        ->maxLength(255)
                                        ->default(mb_strtoupper(Patient::generatePatientUniqueId()))
                                        ->unique(Patient::class, 'patient_unique_id', ignoreRecord: true)
                                        ->disabled()
                                        ->dehydrated()
                                        ->placeholder(__('messages.patient.patient_unique_id')),
                                ])->relationship('patient'),

                                TextInput::make('email')
                                    ->label(__('messages.user.email') . ':')
                                    ->email()
                                    ->required()
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
                                    ->unique(User::class, 'email', ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder(__('messages.user.email')),

                                PhoneInput::make('contact')
                                    ->formatStateUsing(function ($record) {if (! $record?->contact) {return null;}
                                        return '+' . $record->region_code . $record->contact;
                                    })
                                    ->label(__('messages.user.contact_number') . ':')
                                    ->placeholder(__('messages.user.contact_number')),

                                TextInput::make('password')
                                    ->label(new HtmlString(
                                        view('components.help-icon', [
                                            'label' => __('messages.staff.password'),
                                            'tooltip' => __('messages.flash.user_8_or'),
                                        ])->render()
                                    ))
                                    ->password()
                                    ->markAsRequired(false)
                                    ->required()
                                    ->minLength(8)
                                    ->visibleOn('create')
                                    ->revealable()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->confirmed()
                                    ->placeholder(__('messages.staff.password')),

                                TextInput::make('password_confirmation')
                                    ->label(new HtmlString(
                                        view('components.help-icon', [
                                            'label' => __('messages.staff.confirm_password'),
                                            'tooltip' => __('messages.flash.user_8_or'),
                                        ])->render()
                                    ))
                                    ->password()
                                    ->revealable()
                                    ->markAsRequired(false)
                                    ->required()
                                    ->minLength(8)
                                    ->visibleOn('create')
                                    ->maxLength(255)
                                    ->dehydrated(false)
                                    ->placeholder(__('messages.user.confirm_password')),

                                DatePicker::make('dob')
                                    ->label(__('messages.patient.dob') . ':')
                                    ->native(false)
                                    ->displayFormat(datePickerFormat())
                                    ->maxDate(now())
                                    ->placeholder(__('messages.patient.dob')),

                                ToggleButtons::make('gender')
                                    ->label(__('messages.staff.gender') . ':')
                                    ->required()
                                    ->grouped()
                                    ->colors([
                                        1 => Color::Blue,
                                        2 => Color::Pink,
                                    ])
                                    ->icons([
                                        1 => Phosphor::GenderMale,
                                        2 => Phosphor::GenderFemale,
                                    ])
                                    ->inline()
                                    ->options([
                                        1 => __('messages.patient.male'),
                                        2 => __('messages.patient.female'),
                                    ])
                                    ->default(1),

                                Select::make('blood_group')
                                    ->label(__('messages.patient.blood_group') . ':')
                                    ->options(Patient::BLOOD_GROUP_ARRAY)
                                    ->searchable()
                                    ->placeholder(__('messages.doctor.select_blood_group')),

                                // SpatieMediaLibraryFileUpload::make('profile')
                                //     ->label(__('messages.patient.profile') . ':')
                                //     ->image()
                                //     ->avatar()
                                //     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                //     ->disk(config('app.media_disk'))
                                //     ->collection('profile'),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('messages.patient.address_information'))
                    ->relationship('patient')
                    ->schema([
                        Grid::make()
                            ->relationship('address')
                            ->columns(2)
                            ->schema([
                                TextInput::make('address1')
                                    ->label(__('messages.doctor.address1') . ':')
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.address1')),

                                TextInput::make('address2')
                                    ->label(__('messages.doctor.address2') . ':')
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.address2')),

                                Select::make('country_id')
                                    ->label(__('messages.doctor.country') . ':')
                                    ->options(Country::pluck('name', 'id'))
                                    ->live()
                                    ->native(false)
                                    ->searchable()
                                    ->afterStateUpdated(fn (Set $set) => $set('state_id', null))
                                    ->placeholder(__('messages.doctor.country')),

                                Select::make('state_id')
                                    ->label(__('messages.doctor.state') . ':')
                                    ->options(fn (Get $get) => State::where('country_id', $get('country_id'))->pluck('name', 'id'))
                                    ->live()
                                    ->native(false)
                                    ->searchable()
                                    ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                                    ->placeholder(__('messages.doctor.state')),

                                Select::make('city_id')
                                    ->label(__('messages.doctor.city') . ':')
                                    ->native(false)
                                    ->live()
                                    ->options(fn (Get $get) => City::where('state_id', $get('state_id'))->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder(__('messages.doctor.city')),

                                TextInput::make('postal_code')
                                    ->label(__('messages.doctor.postal_code') . ':')
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.postal_code')),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
