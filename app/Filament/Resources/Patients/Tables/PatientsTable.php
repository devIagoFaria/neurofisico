<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Appointment;
use App\Models\MedicineBill;
use App\Models\SmartPatientCards;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use STS\FilamentImpersonate\Actions\Impersonate;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('patient')->withCount('patientAppointments')->whereHas('patient'))
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->columns([
                CustomIdentityColumn::make('first_name')
                    ->avatar(fn ($record) => $record->profile_image)
                    ->html()
                    ->primary(fn ($record) => $record->full_name)
                    ->label(__('messages.user.full_name'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(fn ($record) => PatientResource::getUrl('view', ['record' => $record]))
                    ->secondary('email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name','last_name','email'])
                    ->size('md'),

                TextColumn::make('patient_appointments_count')
                    ->label(__('messages.doctor_dashboard.total_appointments'))
                    ->badge()
                    ->sortable()
                    ->alignCenter(),
                // ToggleColumn::make('email_verified_at')
                //     ->label(__('messages.common.email_verified'))
                //     ->sortable()
                //     ->disabled(fn ($record) => $record->email_verified_at)
                //     ->updateStateUsing(function ($record, $state) {
                //         $state ? $record->email_verified_at = now() : ' ';
                //         $record->save();
                //         notify(__('messages.flash.verified_email'));
                //     })
                //     ->alignCenter(),
                TextColumn::make('created_at')
                    ->label(__('messages.patient.registered_on'))
                    ->dateTime(formattedDate())
                    ->badge()
                    ->sortable(),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_patient'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('messages.web.created_at'))
                    ->placeholder(__('messages.common.pick_date_range'))
            ])
            ->deferFilters(false)
            ->recordActions([
                // Action::make('resend_email_verification')
                //     ->label(__('messages.resend_email_verification'))
                //     ->hiddenLabel()
                //     ->icon(Phosphor::Envelope)
                //     ->iconSize('md')
                //     ->color('info')
                //     ->visible(fn ($record) => $record->email_verified_at === null)
                //     ->modal(false)
                //     ->action(function ($record) {
                //         // Check if user is already verified
                //         if ($record->hasVerifiedEmail()) {
                //             Notification::make()
                //                 ->title(__('messages.flash.user_already_verified'))
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         // Send email verification notification
                //         $record->sendEmailVerificationNotification();

                //         Notification::make()
                //             ->title(__('messages.flash.notification_send'))
                //             ->success()
                //             ->send();
                //     }),
                // Impersonate::make()
                //     ->label(__('messages.common.impersonate'))
                //     ->button()
                //     ->icon(Phosphor::Detective)
                //     ->outlined()
                //     ->redirectTo(route('filament.patient.pages.dashboard'))
                //     ->color(function ($record) {
                //         if ($record->email_verified_at == null || $record->status != User::ACTIVE) {
                //             return 'secondary';
                //         }
                //     })
                //     ->disabled(function ($record) {
                //         if ($record->email_verified_at == null || $record->status != User::ACTIVE) {
                //             return true;
                //         }

                //         return false;
                //     }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(__('messages.flash.patient_delete'))
                    ->before(function (DeleteAction $action, $record) {
                        $patient = $record->patient;
                        if (!$patient) {
                            return;
                        }

                        $usages = [];

                        $appointmentCount = Appointment::where('patient_id', $patient->id)->count();
                        if ($appointmentCount > 0) {
                            $usages[] = "appointments ({$appointmentCount})";
                        }

                        $medicineBillCount = MedicineBill::where('patient_id', $patient->id)->count();
                        if ($medicineBillCount > 0) {
                            $usages[] = "medicine bills ({$medicineBillCount})";
                        }

                        $transactionCount = Transaction::whereHas('appointment', function ($query) use ($patient) {
                            $query->where('patient_id', $patient->id);
                        })->count();
                        if ($transactionCount > 0) {
                            $usages[] = "transactions ({$transactionCount})";
                        }

                        $smartCardCount = SmartPatientCards::whereHas('patient', function ($query) use ($patient) {
                            $query->where('id', $patient->id);
                        })->count();
                        if ($smartCardCount > 0) {
                            $usages[] = "smart patient cards ({$smartCardCount})";
                        }

                        $visitCount = Visit::where('patient_id', $patient->id)->count();
                        if ($visitCount > 0) {
                            $usages[] = "visits ({$visitCount})";
                        }

                        if (!empty($usages)) {
                            Notification::make()
                                ->title(__('messages.flash.patient_cannot_delete'))
                                ->body(__('messages.flash.patient_used'))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
