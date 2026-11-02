<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Models\City;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\Staff;
use App\Models\State;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Actions\Action;
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
class DoctorForm
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
                                Hidden::make('type')->default(User::DOCTOR),
                                TextInput::make('first_name')
                                    ->label(__('messages.doctor.first_name') . ':')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.first_name')),

                                TextInput::make('last_name')
                                    ->label(__('messages.doctor.last_name') . ':')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.last_name')),

                                TextInput::make('email')
                                    ->label(__('messages.user.email') . ':')
                                    ->email()
                                    ->required()
                                    ->unique(User::class, 'email', ignoreRecord: true)
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
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
                                    ->revealable()
                                    ->markAsRequired(false)
                                    ->required()
                                    ->minLength(8)
                                    ->visibleOn('create')
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                                    ->dehydrated(fn($state) => filled($state))
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
                                    ->label(__('messages.doctor.dob') . ':')
                                    ->native(false)
                                    ->displayFormat(datePickerFormat())
                                    ->maxDate(now())
                                    ->placeholder(__('messages.doctor.dob')),

                                // Select::make('specializations')
                                //     ->label(__('messages.doctor.specialization') . ':')
                                //     ->multiple()
                                //     ->afterStateHydrated(function (Select $component, $record, $state) {
                                //         if ($record) {
                                //             $value = $record->doctor->specializations ? $record->doctor->specializations->pluck('id')->toArray() : null;

                                //             $component->state($value);
                                //         } else {
                                //             $component->state(null);
                                //         }
                                //     })
                                //     ->options(Specialization::pluck('name', 'id'))
                                //     ->preload()
                                //     ->required()
                                //     ->placeholder(__('messages.doctor.specialization'))
                                //     ->extraAttributes(function (Get $get, Set $set) {
                                //         return [
                                //             'class' => 'specialization-select-field',
                                //         ];
                                //     })
                                //     ->suffixActions([
                                //         Action::make('add_specialization')
                                //             ->label('')
                                //             ->icon('heroicon-o-plus')
                                //             ->color('primary')
                                //             ->modalHeading(__('messages.specialization.add_specialization'))
                                //             ->modalWidth('md')
                                //             ->form([
                                //                 TextInput::make('name')
                                //                     ->label(__('messages.common.name') . ':')
                                //                     ->placeholder(__('messages.common.name'))
                                //                     ->required()
                                //                     ->rules(['unique:specializations,name'])
                                //                     ->maxLength(255),
                                //             ])
                                //             ->action(function (array $data, Get $get, Set $set) {
                                //                 $specialization = Specialization::create([
                                //                     'name' => $data['name'],
                                //                 ]);

                                //                 $currentSpecializations = $get('specializations') ?? [];
                                //                 $currentSpecializations[] = $specialization->id;

                                //                 $set('specializations', $currentSpecializations);

                                //                 \Filament\Notifications\Notification::make()
                                //                     ->title(__('messages.flash.specialization_create'))
                                //                     ->success()
                                //                     ->send();
                                //             }),
                                //     ]),
                                Group::make([
                                    TextInput::make('experience')
                                        ->label(__('messages.doctor.experience') . ':')
                                        ->numeric()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->minValue(0)
                                        ->suffix('years')
                                        ->maxLength(255)
                                        ->placeholder(__('messages.doctor.experience')),
                                ])->relationship('doctor'),
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
                                        1 => __('messages.doctor.male'),
                                        2 => __('messages.doctor.female'),
                                    ])
                                    ->default(1),

                                Select::make('blood_group')
                                    ->label(__('messages.patient.blood_group') . ':')
                                    ->options(Doctor::BLOOD_GROUP_ARRAY)
                                    ->placeholder(__('messages.doctor.select_blood_group'))
                                    ->native(false),
                                Group::make([
                                    TextInput::make('twitter_url')
                                        ->label(__('messages.doctor.twitter') . ':')
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder(__('messages.common.twitter_url')),
                                ])->relationship('doctor'),
                                Group::make([
                                    TextInput::make('linkedin_url')
                                        ->label(__('messages.doctor.linkedin') . ':')
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder(__('messages.common.linkedin_url')),
                                ])->relationship('doctor'),
                                Group::make([
                                    TextInput::make('instagram_url')
                                        ->label(__('messages.doctor.instagram') . ':')
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder(__('messages.common.instagram_url')),
                                ])->relationship('doctor'),
                                // SpatieMediaLibraryFileUpload::make('profile')
                                //     ->label(__('messages.doctor.profile') . ':')
                                //     ->image()
                                //     ->avatar()
                                //     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                //     ->disk(config('app.media_disk'))
                                //     ->collection(Staff::PROFILE),

                                Toggle::make('status')
                                    ->label(__('messages.doctor.status') . ':')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('messages.doctor.address_information'))
                    ->relationship('address')
                    ->schema([
                        Grid::make()
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
                                    ->afterStateUpdated(fn(Set $set) => $set('state_id', null))
                                    ->placeholder(__('messages.doctor.country')),

                                Select::make('state_id')
                                    ->label(__('messages.doctor.state') . ':')
                                    ->options(fn(Get $get) => State::where('country_id', $get('country_id'))->pluck('name', 'id'))
                                    ->live()
                                    ->native(false)
                                    ->searchable()
                                    ->afterStateUpdated(fn(Set $set) => $set('city_id', null))
                                    ->placeholder(__('messages.doctor.state')),

                                Select::make('city_id')
                                    ->label(__('messages.doctor.city') . ':')
                                    ->native(false)
                                    ->live()
                                    ->options(fn(Get $get) => City::where('state_id', $get('state_id'))->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder(__('messages.doctor.city')),

                                TextInput::make('postal_code')
                                    ->label(__('messages.doctor.postal_code') . ':')
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.postal_code')),
                            ]),
                    ])->columnSpanFull(),
                Section::make(__('messages.doctor.qualification'))
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->schema([
                        Repeater::make('Qualification')
                            ->visibleOn('edit')
                            ->relationship('qualifications')
                            ->label(__('messages.doctor.qualification') . ':')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make(__('messages.doctor.degree')),
                                TableColumn::make(__('messages.doctor.university')),
                                TableColumn::make(__('messages.doctor.year')),
                            ])
                            ->compact()
                            ->addActionLabel(__('messages.common.add'))
                            ->schema([
                                TextInput::make('degree')
                                    ->label(__('messages.doctor.degree'))
                                    ->placeholder(__('messages.doctor.degree'))
                                    ->required(),
                                TextInput::make('university')
                                    ->label(__('messages.doctor.university'))
                                    ->placeholder(__('messages.doctor.university'))
                                    ->required(),
                                Select::make('year')
                                    ->native(false)
                                    ->label(__('messages.doctor.year'))
                                    ->placeholder(__('messages.doctor.year'))
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        $currentYear = \Carbon\Carbon::now()->format('Y');
                                        for ($year = 1960; $year <= $currentYear; $year++) {
                                            $years[$year] = (string) $year;
                                        }

                                        return $years;
                                    })
                                    ->required(),
                            ])
                            ->columnSpanFull()
                            ->columns(3),
                    ]),
            ]);
    }
}
