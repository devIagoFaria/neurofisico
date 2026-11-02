<?php

namespace App\Filament\Resources\Patients\Pages;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Group;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use App\Livewire\PatientAppointmentRelationTable;
use App\Filament\Resources\Patients\PatientResource;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->hidden(fn () => auth()->user()?->hasRole('doctor')),
            backActionResource(static::$resource::getUrl('index')),
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
                                ->imageHeight(80)
                                ->circular()
                                ->defaultImageUrl(fn ($record) =>
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
                                TextEntry::make('full_name')
                                    ->hiddenLabel()
                                    ->size('lg')
                                    ->weight('bold'),
                                TextEntry::make('email')
                                    ->hiddenLabel()
                                    ->formatStateUsing(function ($state, $record) {
                                        return "<a href='mailto:{$state}' class='hover:no-underline'>{$state}</a>";
                                    })
                                    ->html(),
                                TextEntry::make('contact')
                                    ->hiddenLabel()
                                    ->state(function ($record) {

                                        if(! empty($record->contact)){
                                            return '+' . $record->region_code . ' ' . $record->contact;
                                        }

                                        return __('messages.common.n/a');
                                    })
                                    ->default(__('messages.common.n/a')),
                            ])
                            ->columnSpan(4),

                            Group::make([
                                TextEntry::make('today')
                                    ->hiddenLabel()
                                    ->state(fn () => $this->todayAppointmentsCount())
                                    ->formatStateUsing(fn ($state) =>
                                        "<div class='text-left'>
                                            <div class='text-sm'>" .
                                            __('messages.admin_dashboard.today_appointments') .
                                            "</div>
                                            <div class='text-xl font-bold text-primary-600'>{$state}</div>
                                        </div>"
                                    )
                                    ->html()
                                    ->extraAttributes(['class' => 'border rounded-xl p-5']),
                                TextEntry::make('upcoming')
                                    ->hiddenLabel()
                                    ->state(fn () => $this->upcomingAppointmentsCount())
                                    ->formatStateUsing(fn ($state) =>
                                        "<div class='text-left'>
                                            <div class='text-sm'>" .
                                            __('messages.patient_dashboard.upcoming_appointments') .
                                            "</div>
                                            <div class='text-xl font-bold text-primary-600'>{$state}</div>
                                        </div>"
                                    )
                                    ->html()
                                    ->extraAttributes(['class' => 'border rounded-xl p-5']),
                                TextEntry::make('completed')
                                    ->hiddenLabel()
                                    ->state(fn () => $this->completedAppointmentsCount())
                                    ->formatStateUsing(fn ($state) =>
                                        "<div class='text-left'>
                                            <div class='text-sm'>" .
                                            __('messages.patient_dashboard.completed_appointments') .
                                            "</div>
                                            <div class='text-xl font-bold text-primary-600'>{$state}</div>
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
                                ->label(__('messages.patient.blood_group').':')
                                ->state(function ($record) {
                                    $bloodGroup = $record->blood_group;

                                    return $bloodGroup
                                        ? Patient::BLOOD_GROUP_ARRAY[$bloodGroup] ?? __('messages.common.n/a')
                                        : __('messages.common.n/a');
                                }),
                            TextEntry::make('gender')
                                ->label(__('messages.staff.gender'))
                                ->state(function ($record) {
                                    return match ($record->gender) {
                                        User::MALE => __('messages.patient.male'),
                                        User::FEMALE => __('messages.patient.female'),
                                        default => __('messages.common.n/a'),
                                    };
                                })
                                ->default(__('messages.common.n/a')),
                            TextEntry::make('dob')
                                ->label(__('messages.patient.dob').':')
                                ->getStateUsing(fn ($record) => $record->dob ? Carbon::parse($record->dob)->translatedFormat('jS M, Y') : __('messages.common.n/a')),
                            TextEntry::make('patient.address.address1')
                                ->label(__('messages.setting.address').':')
                                ->default(__('messages.common.n/a')),
                            TextEntry::make('created_at')
                                ->label(__('messages.web.created_at').':')
                                ->since(),
                            TextEntry::make('updated_at')
                                ->label(__('messages.patient.last_updated').':')
                                ->since(),
                        ])->columns(2),
                        Tab::make(__('messages.appointments'))->schema([
                            Livewire::make(PatientAppointmentRelationTable::class),
                        ]),
                        Tab::make(__('Prontuario'))->schema([
                            Livewire::make(PatientAppointmentRelationTable::class),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public function todayAppointmentsCount(): int
    {
        $user = $this->getRecord();
        return Appointment::whereHas('patient', fn ($q) =>
            $q->where('user_id', $user->id)
        )
        ->whereDate('date', Carbon::today())
        ->count();
    }

    public function upcomingAppointmentsCount(): int
    {
        $user = $this->getRecord();
        return Appointment::whereHas('patient', fn ($q) =>
            $q->where('user_id', $user->id)
        )
        ->whereDate('date', '>', Carbon::today())
        ->count();
    }

    public function completedAppointmentsCount(): int
    {
        $user = $this->getRecord();
        return Appointment::whereHas('patient', fn ($q) =>
            $q->where('user_id', $user->id)
        )
        ->whereDate('date', '<', Carbon::today())
        ->count();
    }

}
