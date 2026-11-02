<?php

namespace App\Filament\Resources\Visits\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('doctor')) {
                    $doctorId = Doctor::where('user_id', Auth::id())->value('id');
                    $query->where('doctor_id', $doctorId);
                }

                if (Auth::user()->hasRole('patient')) {
                    $patientId = Patient::where('user_id', Auth::id())->value('id');
                    $query->where('patient_id', $patientId);
                }
            })
            ->defaultSort('id', 'desc')
            ->recordUrl(null)
            ->columns([
                CustomIdentityColumn::make('doctor.user.full_name')
                    ->avatar(fn($record) => $record->doctor?->user?->profile_image)
                    ->html()
                    ->primary(fn($record) => $record->doctor?->user?->full_name . ' ' . rating($record->doctor?->reviews?->avg('rating') ?? 0))
                    ->label(__('messages.doctor.doctor'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(
                        fn($record) =>
                        Auth::user()->hasRole('patient') ? null : DoctorResource::getUrl('view', ['record' => $record->doctor->user_id])
                    )
                    ->secondary('doctor.user.email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->size('md')
                    ->hidden(fn($record) => Auth::user()->hasRole('doctor')),
                CustomIdentityColumn::make('patient.user.full_name')
                    ->avatar(fn($record) => $record->patient?->user?->profile_image)
                    ->html()
                    ->primary(fn($record) => $record->patient?->user?->full_name)
                    ->label(__('messages.appointment.patient'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                   ->primaryUrl(function ($record) {
                        $user = Auth::user();

                        if ($user->hasRole('clinic_admin')) {
                            return route(
                                'filament.admin.resources.patients.view',
                                ['record' => $record->patient->user_id]
                            );
                        }

                        if ($user->hasRole('doctor')) {
                            return route(
                                'filament.doctor.resources.patients.view',
                                ['record' => $record->patient->id]
                            );
                        }

                        return null;
                    })
                    ->secondary('patient.user.email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->size('md')
                    ->hidden(fn($record) => Auth::user()->hasRole('patient')),
                TextColumn::make('visit_date')
                    ->label(__('messages.visit.visit_date'))
                    ->date('d M Y')
                    ->badge()
                    ->sortable()
                    ->searchable(),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_visit'))
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->color('primary'),
                EditAction::make()
                    ->iconButton()
                    ->hidden(fn($record) => Auth::user()->hasRole('patient')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.visit_delete'))
                    ->hidden(fn($record) => Auth::user()->hasRole('patient')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
