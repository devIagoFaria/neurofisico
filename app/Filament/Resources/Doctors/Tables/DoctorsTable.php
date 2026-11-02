<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Appointment;
use App\Models\DoctorHoliday;
use App\Models\DoctorSession;
use App\Models\LiveConsultation;
use App\Models\MedicineBill;
use App\Models\Prescription;
use App\Models\Qualification;
use App\Models\Review;
use App\Models\User;
use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use STS\FilamentImpersonate\Actions\Impersonate;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('doctor')->whereHas('doctor'))
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->columns([
                CustomIdentityColumn::make('first_name')
                    ->avatar(fn ($record) => $record->profile_image)
                    ->html()
                    ->primary(fn ($record) => $record->full_name.' '.rating($record->doctor->reviews?->avg('rating') ?? 0))
                    ->label(__('messages.doctor.doctor'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(fn ($record) => DoctorResource::getUrl('view', ['record' => $record]))
                    ->secondary('email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->size('md'),

                ToggleColumn::make('status')
                    ->label(__('messages.doctor.status'))
                    ->afterStateUpdated(function ($record, $state) {
                        notify(__('messages.flash.status_update'));
                    }),
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
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                SelectFilter::make('status')
                        ->options([
                            User::DEACTIVE => __('messages.common.de_active'),
                            User::ACTIVE => __('messages.common.active'),
                        ])
                    ->native(false)
                    ->label(__('messages.web.status')),
            ])
            ->deferFilters(false)
            ->emptyStateHeading(__('messages.custom_table_message.no_doctor'))
            ->recordActions([
                // Action::make('add_qualification')
                //     ->label(__('messages.doctor.add_qualification'))
                //     ->iconButton()
                //     ->requiresConfirmation(false)
                //     ->modalIcon(' ')
                //     ->modalDescription(new HtmlString('<span></span>'))
                //     ->icon(Phosphor::Plus)
                //     ->form([
                //         TextInput::make('degree')->label(__('messages.doctor.degree'))->placeholder(__('messages.doctor.degree'))->required(),
                //         TextInput::make('university')->label(__('messages.doctor.university'))->placeholder(__('messages.doctor.university'))->required(),
                //         Select::make('year')
                //             ->native(false)
                //             ->label(__('messages.doctor.year'))
                //             ->placeholder(__('messages.doctor.year'))
                //             ->preload()
                //             ->options(function () {
                //                 $currentYear = \Carbon\Carbon::now()->format('Y');
                //                 for ($year = 1960; $year <= $currentYear; $year++) {
                //                     $years[$year] = (string) $year;
                //                 }

                //                 return $years;
                //             })
                //             ->searchable()
                //             ->required(),
                //     ])
                //     ->action(function ($record, $data) {
                //         Qualification::create($data + ['user_id' => $record->id]);
                //     })
                //     ->modalSubmitActionLabel(__('messages.common.add'))
                //     ->requiresConfirmation(),
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(__('messages.flash.doctor_delete'))
                    ->before(function (DeleteAction $action, $record) {
                        $doctor = $record->doctor;
                        if (!$doctor) {
                            return;
                        }

                        $usages = [];

                        $appointmentCount = Appointment::where('doctor_id', $doctor->id)->count();
                        if ($appointmentCount > 0) {
                            $usages[] = "appointments ({$appointmentCount})";
                        }

                        $medicineBillCount = MedicineBill::where('doctor_id', $doctor->id)->count();
                        if ($medicineBillCount > 0) {
                            $usages[] = "medicine bills ({$medicineBillCount})";
                        }

                        $visitCount = Visit::where('doctor_id', $doctor->id)->count();
                        if ($visitCount > 0) {
                            $usages[] = "visits ({$visitCount})";
                        }

                        $liveConsultationCount = LiveConsultation::where('doctor_id', $doctor->id)->count();
                        if ($liveConsultationCount > 0) {
                            $usages[] = "live consultations ({$liveConsultationCount})";
                        }

                        $prescriptionCount = Prescription::where('doctor_id', $doctor->id)->count();
                        if ($prescriptionCount > 0) {
                            $usages[] = "prescriptions ({$prescriptionCount})";
                        }

                        $reviewCount = Review::where('doctor_id', $doctor->id)->count();
                        if ($reviewCount > 0) {
                            $usages[] = "reviews ({$reviewCount})";
                        }

                        $doctorSessionCount = DoctorSession::where('doctor_id', $doctor->id)->count();
                        if ($doctorSessionCount > 0) {
                            $usages[] = "doctor sessions ({$doctorSessionCount})";
                        }

                        $doctorHolidayCount = DoctorHoliday::where('doctor_id', $doctor->id)->count();
                        if ($doctorHolidayCount > 0) {
                            $usages[] = "doctor holidays ({$doctorHolidayCount})";
                        }

                        if (!empty($usages)) {
                            Notification::make()
                                ->title(__('messages.flash.doctor_cannot_delete'))
                                ->body(__('messages.flash.doctor_used'))
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
