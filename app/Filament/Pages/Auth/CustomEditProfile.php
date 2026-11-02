<?php

namespace App\Filament\Pages\Auth;

use App\Models\City;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\State;
use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Filament\Forms\Components\PhoneInput;

class CustomEditProfile extends EditProfile
{
    public static function getLabel(): string
    {
        return __('messages.user.edit_profile');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('profile')
                                    ->label(__('messages.doctor.profile') . ':')
                                    ->image()
                                    ->avatar()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->disk(config('app.media_disk'))
                                    ->collection(User::PROFILE),
                                TextInput::make('first_name')
                                    ->label(__('messages.doctor.first_name') . ':')
                                    ->placeholder(__('messages.doctor.first_name'))
                                    ->required()
                                    ->autofocus(),

                                TextInput::make('last_name')
                                    ->label(__('messages.doctor.last_name') . ':')
                                    ->placeholder(__('messages.doctor.last_name'))
                                    ->required(),

                                TextInput::make('email')
                                    ->label(__('messages.user.email') . ':')
                                    ->placeholder(__('messages.user.email'))
                                    ->required()
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
                                    ->email()
                                    ->unique(User::class, 'email', fn($record) => $record),

                                PhoneInput::make('contact')
                                    ->label(__('messages.user.contact_number') . ':')
                                    ->placeholder(__('messages.user.contact_number'))
                                    ->required(),

                                Radio::make('gender')
                                    ->label(__('messages.user.gender') . ':')
                                    ->options([
                                        User::MALE => __('messages.patient.male'),
                                        User::FEMALE => __('messages.patient.female'),
                                    ])
                                    ->inline()
                                    ->required(),
                            ]),
                        Group::make()
                            ->schema([
                                DatePicker::make('dob')
                                    ->label(__('messages.doctor.dob') . ':')
                                    ->inlineLabel(false)
                                    ->native(false)
                                    ->displayFormat(datePickerFormat())
                                    ->maxDate(now())
                                    ->placeholder(__('messages.doctor.dob')),

                                Select::make('blood_group')
                                    ->label(__('messages.patient.blood_group') . ':')
                                    ->inlineLabel(false)
                                    ->options(Doctor::BLOOD_GROUP_ARRAY)
                                    ->placeholder(__('messages.doctor.select_blood_group'))
                                    ->native(false),

                                TextInput::make('address1')
                                    ->label(__('messages.doctor.address1') . ':')
                                    ->inlineLabel(false)
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.address1')),

                                TextInput::make('address2')
                                    ->label(__('messages.doctor.address2') . ':')
                                    ->inlineLabel(false)
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.address2')),

                                Select::make('country_id')
                                    ->label(__('messages.doctor.country') . ':')
                                    ->inlineLabel(false)
                                    ->options(Country::pluck('name', 'id'))
                                    ->live()
                                    ->native(false)
                                    ->searchable()
                                    ->afterStateUpdated(fn(Set $set) => $set('state_id', null))
                                    ->placeholder(__('messages.doctor.country')),

                                Select::make('state_id')
                                    ->label(__('messages.doctor.state') . ':')
                                    ->inlineLabel(false)
                                    ->options(fn(Get $get) => State::where('country_id', $get('country_id'))->pluck('name', 'id'))
                                    ->live()
                                    ->native(false)
                                    ->searchable()
                                    ->afterStateUpdated(fn(Set $set) => $set('city_id', null))
                                    ->placeholder(__('messages.doctor.state')),

                                Select::make('city_id')
                                    ->label(__('messages.doctor.city') . ':')
                                    ->inlineLabel(false)
                                    ->native(false)
                                    ->live()
                                    ->options(fn(Get $get) => City::where('state_id', $get('state_id'))->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder(__('messages.doctor.city')),

                                TextInput::make('postal_code')
                                    ->label(__('messages.doctor.postal_code') . ':')
                                    ->inlineLabel(false)
                                    ->maxLength(255)
                                    ->placeholder(__('messages.doctor.postal_code')),
                            ])->columns(2),
                    ]),
            ]);
    }

    protected function getRedirectUrl(): ?string
    {
        if (auth()->user()->hasRole('admin')) {
            return route('filament.admin.pages.dashboard');
        }

        if (auth()->user()->hasRole('doctor')) {
            return route('filament.doctor.pages.dashboard');
        }

        if (auth()->user()->hasRole('patient')) {
            return route('filament.patient.pages.dashboard');
        }

        return route('filament.admin.pages.dashboard');
    }

    protected function beforeSave(): void
    {
        if (getLogInUser()->is_default) {
            Notification::make()
                ->title(__('messages.common.error_default_records'))
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = Auth::user();

        if ($user->hasRole('patient') && $user->patient?->address) {
            $address = $user->patient->address;
        } elseif ($user->hasRole('doctor') && $user->doctor?->address) {
            $address = $user->doctor->address;
        } else {
            $address = $user->address ?? null;
        }

        if ($address) {
            $data = array_merge($data, [
                'address1'    => $address->address1,
                'address2'    => $address->address2,
                'country_id'  => $address->country_id,
                'state_id'    => $address->state_id,
                'city_id'     => $address->city_id,
                'postal_code' => $address->postal_code,
            ]);
        }

        return $data;
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $addressInputArray = Arr::only($data, [
                'address1',
                'address2',
                'city_id',
                'state_id',
                'country_id',
                'postal_code',
            ]);

            if ($user->hasRole('clinic_admin')) {

                $user->update($data);

                if (! empty($data['profile'])) {
                    $user->clearMediaCollection(User::PROFILE);
                    $user->addMedia($data['profile'])
                        ->toMediaCollection(User::PROFILE, config('app.media_disk'));
                }

                $user->address()->updateOrCreate(['owner_id' => $user->id], $addressInputArray);
            } elseif ($user->hasRole('patient')) {

                $patient = $user->patient;

                $data['type']  = User::PATIENT;
                $data['email'] = setEmailLowerCase($data['email']);

                $patient->user()->update(
                    Arr::except($data, [
                        'address1',
                        'address2',
                        'city_id',
                        'state_id',
                        'country_id',
                        'postal_code',
                        'profile',
                    ])
                );

                $patient->address()->updateOrCreate(['owner_id' => $patient->id], $addressInputArray);

                if (! empty($data['profile'])) {
                    $patient->clearMediaCollection(Patient::PROFILE);
                    $patient->addMedia($data['profile'])
                        ->toMediaCollection(Patient::PROFILE, config('app.media_disk'));
                }
            } elseif ($user->hasRole('doctor')) {

                $doctor = $user->doctor;

                $data['type']  = User::DOCTOR;
                $data['email'] = setEmailLowerCase($data['email']);

                $doctor->user()->update(
                    Arr::except($data, [
                        'address1',
                        'address2',
                        'city_id',
                        'state_id',
                        'country_id',
                        'postal_code',
                        'profile',
                    ])
                );

                $doctor->address()->updateOrCreate(['owner_id' => $doctor->id], $addressInputArray);

                if (! empty($data['profile'])) {
                    $user->clearMediaCollection(User::PROFILE);
                    $user->addMedia($data['profile'])
                        ->toMediaCollection(User::PROFILE, config('app.media_disk'));
                }
            } elseif (
                $user->hasRole('doctor_independiente') || $user->hasRole('reception') || $user->hasRole('admin')
                || $user->hasRole('accounting') || $user->hasRole('receptionist') || $user->hasRole('pharmasist')
            ) {

                $staff = $user;

                $data['type']  = User::STAFF;
                $data['email'] = setEmailLowerCase($data['email']);

                $staff->update(
                    Arr::except($data, [
                        'address1',
                        'address2',
                        'city_id',
                        'state_id',
                        'country_id',
                        'postal_code',
                        'profile',
                    ])
                );

                $staff->address()->updateOrCreate(['owner_id' => $staff->id], $addressInputArray);

                if (! empty($data['profile'])) {
                    $staff->clearMediaCollection(Staff::PROFILE);
                    $staff->addMedia($data['profile'])
                        ->toMediaCollection(Staff::PROFILE, config('app.media_disk'));
                }
            }

            DB::commit();

            return $record;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.user_profile_update');
    }
}
