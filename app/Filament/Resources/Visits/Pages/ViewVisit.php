<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Resources\Visits\VisitsResource;
use App\Livewire\VisitNoteTable;
use App\Livewire\VisitObservationTable;
use App\Livewire\VisitPrescriptionTable;
use App\Livewire\VisitProblemTable;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ViewVisit extends ViewRecord
{
    protected static string $resource = VisitsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->hidden(fn () => Auth::user()->hasRole('patient')),
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('patient.user.full_name')
                            ->label(__('messages.web.patient_name'))
                            ->hidden(fn () => Auth::user()->hasRole('patient')),
                        TextEntry::make('patient.user.email')
                            ->label(__('messages.web.email'))
                            ->hidden(fn () => Auth::user()->hasRole('patient')),
                        TextEntry::make('doctor.user.full_name')
                            ->label(__('messages.doctor_session.doctor'))
                            ->visible(fn () => Auth::user()->hasRole('patient')),
                        TextEntry::make('doctor.user.email')
                            ->label(__('messages.web.email'))
                            ->visible(fn () => Auth::user()->hasRole('patient')),
                        SpatieMediaLibraryImageEntry::make('patient.user.profile')
                            ->label(__('messages.patient.profile'))
                            ->collection(User::PROFILE)
                            ->defaultImageUrl(
                                fn ($record) => ! $record->patient?->user?->hasMedia(User::PROFILE)
                                    ? getUserImageInitial(
                                        $record->patient->user->id,
                                        $record->patient->user->full_name
                                    )
                                    : null
                            )
                            ->circular()
                            ->imageWidth(50)
                            ->imageHeight(50)
                            ->hidden(fn () => Auth::user()->hasRole('patient')),
                        SpatieMediaLibraryImageEntry::make('doctor.user.profile')
                            ->label(__('messages.doctor.profile'))
                            ->collection(User::PROFILE)
                            ->defaultImageUrl(
                                fn ($record) => ! $record->doctor?->user?->hasMedia(User::PROFILE)
                                    ? getUserImageInitial(
                                        $record->doctor->user->id,
                                        $record->doctor->user->full_name
                                    )
                                    : null
                            )
                            ->circular()
                            ->imageWidth(50)
                            ->imageHeight(50)
                            ->disk(config('app.media_disk'))
                            ->visible(fn () => Auth::user()->hasRole('patient')),
                        TextEntry::make('doctor.user.full_name')
                            ->label(__('messages.doctor_session.doctor'))
                            ->visible(fn () => Auth::user()->hasRole('clinic_admin')),
                        TextEntry::make('visit_date')
                            ->label(__('messages.visit.visit_date'))
                            ->getStateUsing(fn ($record) => $record->visit_date ? Carbon::parse($record->visit_date)->translatedFormat('jS M, Y') : __('messages.common.n/a')),
                        TextEntry::make('created_at')
                            ->label(__('messages.web.created_at'))
                            ->since(),
                        TextEntry::make('updated_at')
                            ->label(__('messages.patient.last_updated'))
                            ->since(),
                        TextEntry::make('description')
                            ->label(__('messages.appointment.description'))
                            ->default(__('messages.common.n/a'))
                            ->hidden(fn () => Auth::user()->hasRole('clinic_admin')),
                    ]),
                Tabs::make(__('Details'))
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make(__('messages.visit.problems'))
                            ->schema([
                                Livewire::make(VisitProblemTable::class),
                            ]),
                        Tabs\Tab::make(__('messages.visit.observations'))
                            ->schema([
                                Livewire::make(VisitObservationTable::class),
                            ]),
                        Tabs\Tab::make(__('messages.visit.notes'))
                            ->schema([
                                Livewire::make(VisitNoteTable::class),
                            ]),
                        Tabs\Tab::make(__('messages.visit.prescriptions'))
                            ->schema([
                                Livewire::make(VisitPrescriptionTable::class),
                            ]),
                    ]),
            ]);
    }
}
