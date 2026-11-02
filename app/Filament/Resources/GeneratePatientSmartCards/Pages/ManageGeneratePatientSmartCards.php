<?php

namespace App\Filament\Resources\GeneratePatientSmartCards\Pages;

use App\Filament\Resources\GeneratePatientSmartCards\GeneratePatientSmartCardResource;
use App\Models\Patient;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageGeneratePatientSmartCards extends ManageRecords
{
    protected static string $resource = GeneratePatientSmartCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('md')
                ->createAnother(false)
                ->label(__('messages.smart_patient_card.generate_patient_card'))
                ->successNotificationTitle(null)
                ->action(function ($data) {
                    try {
                        DB::beginTransaction();
                        $updatedCount = 0;

                        if ($data['status'] == 1) { // ALL_PATIENT
                            $patientsWithoutCards = Patient::whereNotNull('user_id')
                                ->whereHas('user')
                                ->where(function($query) {
                                    $query->whereNull('template_id')
                                          ->orWhere('template_id', '=', 0);
                                })
                                ->get();

                            if ($patientsWithoutCards->isEmpty()) {
                                $updatedCount = 0;
                            } else {
                                Patient::whereNotNull('user_id')
                                    ->whereHas('user')
                                    ->where(function($query) {
                                        $query->whereNull('template_id')
                                              ->orWhere('template_id', '=', 0);
                                    })
                                    ->update(['template_id' => $data['template_id']]);

                                $allPatients = Patient::whereNotNull('user_id')->whereHas('user')->get();
                                foreach ($allPatients as $patient) {
                                    Patient::where('id', $patient->id)->update([
                                        'qr_code' => route('patient_show').'/'.$patient->patient_unique_id,
                                    ]);
                                }
                                $updatedCount = $patientsWithoutCards->count();
                            }
                        }

                        if ($data['status'] == 2) { // ONLY_ONE_PATIENT
                            $patient = Patient::where('user_id', $data['patient_id'])->first();
                            if ($patient) {
                                Patient::where('user_id', $data['patient_id'])->update(['template_id' => $data['template_id']]);
                                Patient::where('user_id', $data['patient_id'])->update([
                                    'qr_code' => route('patient_show').'/'.$patient->patient_unique_id,
                                ]);
                                $updatedCount = 1;
                            } else {
                                $updatedCount = 0;
                            }
                        }

                        if ($data['status'] == 3) { // REMANING_PATIENT
                            $remainingPatients = Patient::where('template_id', null)->where('user_id', '!=', null)->get();
                            if ($remainingPatients->count() > 0) {
                                $all = Patient::select('id', 'patient_unique_id')->where('template_id', null)->get()->pluck('patient_unique_id', 'id');
                                foreach ($all as $key => $value) {
                                    Patient::where('id', $key)->update([
                                        'qr_code' => route('patient_show').'/'.$value,
                                    ]);
                                }
                                Patient::where('template_id', null)->update(['template_id' => $data['template_id']]);
                                $updatedCount = $remainingPatients->count();
                            } else {
                                $updatedCount = 0;
                            }
                        }

                        DB::commit();

                        if ($updatedCount > 0) {
                            Notification::make()
                                ->title(__('messages.smart_patient_card.patient_smart_card_created'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('messages.smart_patient_card.no_patients_available'))
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();

                        Notification::make()
                            ->title(__('Something went wrong'))
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
