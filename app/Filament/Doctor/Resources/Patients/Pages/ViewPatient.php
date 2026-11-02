<?php

namespace App\Filament\Doctor\Resources\Patients\Pages;

use App\Filament\Doctor\Resources\Patients\PatientResource;
use App\Livewire\PatientAppointmentRelationTable;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(route('filament.doctor.resources.appointments.index')),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(12)->schema([
                            SpatieMediaLibraryImageEntry::make('profile_image')
                                ->collection(User::PROFILE)
                                ->hiddenLabel()
                                ->imageWidth(80)
                                ->disk(config('app.media_disk'))
                                ->imageHeight(80)
                                ->circular()
                                ->defaultImageUrl(
                                    fn($record) =>
                                    ! $record->hasMedia(User::PROFILE)
                                        ? getUserImageInitial($record->id, $record->full_name)
                                        : null
                                )
                                ->columnSpan(1),
                            Group::make([
                                TextEntry::make('patient_label')
                                    ->hiddenLabel()
                                    ->state('Patient')
                                    ->color('success'),
                                TextEntry::make('user.full_name')
                                    ->hiddenLabel()
                                    ->size('lg')
                                    ->weight('bold'),
                                TextEntry::make('user.email')
                                    ->hiddenLabel()
                                    ->formatStateUsing(function ($state, $record) {
                                        return "<a href='mailto:{$state}' class='hover:no-underline'>{$state}</a>";
                                    })
                                    ->html(),
                                PhoneEntry::make('user.contact')
                                    ->hiddenLabel()
                                    ->formatStateUsing(function ($state, $record) {
                                        if (str_starts_with($state, '+') && strlen($state) > 4) {
                                            return $state;
                                        }
                                        if (empty($record->user->contact)) {
                                            return __('messages.common.n/a');
                                        }

                                        return $record->user->region_code . $record->user->contact;
                                    })
                                    ->default(__('messages.common.n/a')),
                            ])
                                ->columnSpan(4),

                            Group::make([
                                TextEntry::make('today')
                                    ->hiddenLabel()
                                    ->state(fn() => $this->todayAppointmentsCount())
                                    ->formatStateUsing(
                                        fn($state) =>
                                        "<div class='text-left'>
                                            <div class='text-xl font-bold text-primary-600'>{$state}</div>
                                            <div class='text-sm'>" .
                                            __('messages.admin_dashboard.today_appointments') .
                                            "</div>
                                        </div>"
                                    )
                                    ->html()
                                    ->extraAttributes(['class' => 'border rounded-xl p-5']),
                                TextEntry::make('upcoming')
                                    ->hiddenLabel()
                                    ->state(fn() => $this->upcomingAppointmentsCount())
                                    ->formatStateUsing(
                                        fn($state) =>
                                        "<div class='text-left'>
                                            <div class='text-xl font-bold text-primary-600'>{$state}</div>
                                            <div class='text-sm'>" .
                                            __('messages.patient_dashboard.upcoming_appointments') .
                                            "</div>
                                        </div>"
                                    )
                                    ->html()
                                    ->extraAttributes(['class' => 'border rounded-xl p-5']),
                                TextEntry::make('completed')
                                    ->hiddenLabel()
                                    ->state(fn() => $this->completedAppointmentsCount())
                                    ->formatStateUsing(
                                        fn($state) =>
                                        "<div class='text-left'>
                                            <div class='text-xl font-bold text-primary-600'>{$state}</div>
                                            <div class='text-sm'>" .
                                            __('messages.patient_dashboard.completed_appointments') .
                                            "</div>
                                        </div>"
                                    )
                                    ->html()
                                    ->extraAttributes(['class' => 'border rounded-xl p-5']),
                            ])
                                ->columns(3)
                                ->columnSpan(7)
                                ->extraAttributes(['class' => 'justify-end']),
                        ]),
                    ])
                    ->columnSpanFull(),
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('messages.common.overview'))->schema([
                            TextEntry::make('blood_group')
                                ->label(__('messages.patient.blood_group') . ':')
                                ->state(function ($record) {
                                    $bloodGroup = $record->user->blood_group;

                                    return $bloodGroup
                                        ? Patient::BLOOD_GROUP_ARRAY[$bloodGroup] ?? __('messages.common.n/a')
                                        : __('messages.common.n/a');
                                }),
                            TextEntry::make('gender')
                                ->label(__('messages.user.gender') . ':')
                                ->state(function ($record) {
                                    $gender = $record->user->gender;

                                    return $gender
                                        ? User::GENDER[$gender] ?? __('messages.common.n/a')
                                        : __('messages.common.n/a');
                                }),
                            TextEntry::make('dob')
                                ->label(__('messages.patient.dob') . ':')
                                ->getStateUsing(fn($record) => $record->user->dob ? Carbon::parse($record->user->dob)->translatedFormat('jS M, Y') : __('messages.common.n/a')),
                            TextEntry::make('address.address1')
                                ->label(__('messages.setting.address') . ':')
                                ->default(__('messages.common.n/a')),
                            TextEntry::make('created_at')
                                ->label(__('messages.web.created_at') . ':')
                                ->since(),
                            TextEntry::make('updated_at')
                                ->label(__('messages.patient.last_updated') . ':')
                                ->since(),
                        ])->columns(2),
                        Tab::make(__('messages.appointments'))->schema([
                            Livewire::make(PatientAppointmentRelationTable::class),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public function todayAppointmentsCount(): int
    {
        return Appointment::where('patient_id', $this->getRecord()->id)
            ->whereDate('date', Carbon::today())
            ->count();
    }

    public function upcomingAppointmentsCount(): int
    {
        return Appointment::where('patient_id', $this->getRecord()->id)
            ->whereDate('date', '>', Carbon::today())
            ->count();
    }

    public function completedAppointmentsCount(): int
    {
        return Appointment::where('patient_id', $this->getRecord()->id)
            ->whereDate('date', '<', Carbon::today())
            ->count();
    }
}
