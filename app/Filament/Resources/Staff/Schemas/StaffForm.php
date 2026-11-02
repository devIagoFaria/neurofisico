<?php

namespace App\Filament\Resources\Staff\Schemas;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Forms\Components\PhoneInput;
use Illuminate\Support\HtmlString;
class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(__('messages.staff.first_name') . ':')
                                    ->required()
                                    ->placeholder(__('messages.staff.first_name'))
                                    ->maxLength(255),

                                TextInput::make('last_name')
                                    ->label(__('messages.staff.last_name') . ':')
                                    ->required()
                                    ->placeholder(__('messages.staff.last_name'))
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label(__('messages.staff.email') . ':')
                                    ->email()
                                    ->required()
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
                                    ->placeholder(__('messages.staff.email'))
                                    ->maxLength(255),

                                PhoneInput::make('contact')
                                    ->formatStateUsing(function ($record) {if (! $record?->contact) {return null;}
                                        return '+' . $record->region_code . $record->contact;
                                    })
                                    ->label(__('messages.user.contact_number') . ':')
                                    ->placeholder(__('messages.user.contact_number')),
                            ]),

                        Grid::make(2)
                            ->schema([
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
                                    ->placeholder(__('messages.staff.password'))
                                    ->revealable()
                                    ->maxLength(255)
                                    ->live()
                                    ->visibleOn('create')
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (empty($state)) {
                                            $set('password_confirmation', '');
                                        }
                                    }),

                                TextInput::make('password_confirmation')
                                    ->label(new HtmlString(
                                        view('components.help-icon', [
                                            'label' => __('messages.staff.confirm_password'),
                                            'tooltip' => __('messages.flash.user_8_or'),
                                        ])->render()
                                    ))
                                    ->password()
                                    ->markAsRequired(false)
                                    ->required(fn ($get) => ! empty($get('password')))
                                    ->placeholder(__('messages.staff.confirm_password'))
                                    ->revealable()
                                    ->visibleOn('create')
                                    ->same('password')
                                    ->maxLength(255),
                            ])->visibleOn('create'),

                        Grid::make(2)
                            ->schema([
                                Select::make('role_id')
                                    ->label(__('messages.staff.role') . ':')
                                    ->afterStateHydrated(function ($record, $state, $component, $operation) {
                                        if ($operation === 'edit') {
                                            $component->state($record->roles()->first()->name);
                                        }
                                    })
                                    ->options(function () {
                                        return Role::where('is_default', '!=', true)->get()->pluck('display_name', 'name')->except([User::ADMIN, User::DOCTOR, User::PATIENT])->toArray();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->preload()
                                    ->native(false)
                                    ->required(),

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
                                        1 => __('messages.staff.male'),
                                        2 => __('messages.staff.female'),
                                    ])
                                    ->default(1),
                            ]),

                        SpatieMediaLibraryFileUpload::make('profile')
                            ->label(__('messages.doctor.profile') . ':')
                            ->image()
                            ->avatar()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->disk(config('app.media_disk'))
                            ->collection(Staff::PROFILE),
                    ]),
            ])->columns(1);
    }
}
