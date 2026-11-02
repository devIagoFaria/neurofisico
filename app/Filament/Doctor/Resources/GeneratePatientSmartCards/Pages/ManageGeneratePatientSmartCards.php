<?php

namespace App\Filament\Doctor\Resources\GeneratePatientSmartCards\Pages;

use App\Filament\Doctor\Resources\GeneratePatientSmartCards\GeneratePatientSmartCardsResource;
use App\Models\Patient;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageGeneratePatientSmartCards extends ManageRecords
{
    protected static string $resource = GeneratePatientSmartCardsResource::class;

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

                        if ($data['status'] == 1) { // ALL_PATIENT
                            Patient::where('user_id', '!=', null)->update(['template_id' => $data['template_id']]);
                            $all = Patient::select('id', 'patient_unique_id')->get()->pluck('patient_unique_id', 'id');
                            foreach ($all as $key => $value) {
                                Patient::where('id', $key)->update([
                                    'qr_code' => route('patient_show') . '/' . $value,
                                ]);
                            }
                        }

                        if ($data['status'] == 2) { // ONLY_ONE_PATIENT
                            $patientData = Patient::where('user_id', $data['patient_id'])->first();
                            Patient::where('user_id', $data['patient_id'])->update(['template_id' => $data['template_id']]);
                            Patient::where('user_id', $data['patient_id'])->update([
                                'qr_code' => route('patient_show') . '/' . $patientData->patient_unique_id,
                            ]);
                        }

                        if ($data['status'] == 3) { // REMANING_PATIENT
                            $all = Patient::select('id', 'patient_unique_id')->where('template_id', null)->get()->pluck('patient_unique_id', 'id');
                            foreach ($all as $key => $value) {
                                Patient::where('id', $key)->update([
                                    'qr_code' => route('patient_show') . '/' . $value,
                                ]);
                            }
                            Patient::where('template_id', null)->update(['template_id' => $data['template_id']]);
                        }

                        DB::commit();

                        Notification::make()
                            ->title(__('messages.smart_patient_card.patient_smart_card_created'))
                            ->success()
                            ->send();
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
