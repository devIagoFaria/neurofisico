<?php

namespace App\Filament\Resources\MedicineBills\Pages;

use Exception;
use App\Models\Medicine;
use App\Models\MedicineBill;
use App\Models\SaleMedicine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Http\Controllers\MedicineBillController;
use App\Filament\Resources\MedicineBills\MedicineBillsResource;
use App\Models\Patient;

class CreateMedicineBills extends CreateRecord
{
    protected static string $resource = MedicineBillsResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createPatient')
                ->label(__('messages.patient.add'))
                ->icon('heroicon-m-plus')
                ->modalHeading(__('messages.patient.add'))
                ->modalSubmitActionLabel(__('messages.common.save'))
                ->form(Patient::schema())
                ->action(function (array $data) {
                    $user = User::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'contact' => $data['contact'],
                        'gender' => $data['gender'],
                        'status' => $data['status'] ?? true,
                        'password' => bcrypt($data['password']),
                        'type' => User::PATIENT,
                    ]);

                    $patient = Patient::create([
                        'user_id' => $user->id,
                        'patient_unique_id' => mb_strtoupper(Patient::generatePatientUniqueId()),
                    ]);

                    $this->form->fill([
                        'patient_id' => $patient->id,
                    ]);

                    Notification::make()
                        ->title(__('messages.flash.patient_create'))
                        ->success()
                        ->send();
                }),
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.medicine_bill_create');
    }

    protected function beforeValidate(): array
    {
        $data = $this->form->getState();
        if (empty($data['saleMedicine'] ?? [])) {
            Notification::make()
                ->title(__('messages.medicine_bills.medicine_not_selected'))
                ->danger()
                ->send();

            $this->halt();
        }

        // Duplicate medicine check
        $medicineIds = collect($data['saleMedicine'])->pluck('medicine_id');
        if ($medicineIds->duplicates()->isNotEmpty()) {
            Notification::make()
                ->title(__('messages.medicine_bills.duplicate_medicine'))
                ->danger()
                ->send();

            $this->halt();
        }

        foreach ($data['saleMedicine'] as $item) {
            $medicine = Medicine::find($item['medicine_id']);

            if (!$medicine) {
                Notification::make()
                    ->title(__('messages.medicine_bills.invalid_medicine'))
                    ->danger()
                    ->send();

                $this->halt();
            }

            $available = (int) ($medicine->available_quantity ?? 0);
            $requested = (int) ($item['sale_quantity'] ?? 0);

            if ($available === 0) {
                Notification::make()
                    ->title(
                        __('messages.medicine_bills.available_quantity') .
                            ' ' . $medicine->name . ' is' . ' 0.'
                    )
                    ->danger()
                    ->send();

                $this->halt();
            }

            if ($available < $requested) {
                Notification::make()
                    ->title(
                        __('messages.medicine_bills.available_quantity') .
                            ' ' . $medicine->name . ' ' . 'is ' . $available . '.'
                    )
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total'] = (float) ($data['total'] ?? 0);
        $data['discount'] = (float) ($data['discount'] ?? 0);
        $data['tax_amount'] = (float) ($data['tax_amount'] ?? 0);
        $data['net_amount'] = (float) ($data['net_amount'] ?? 0);
        $data['payment_status'] = !empty($data['payment_status']) ? 1 : 0;

        return $data;
    }
    protected function afterCreate(): void
    {
        $medicineBill = $this->record;
        $medicineBill->update(['model_id' => $medicineBill->id]);
    }

    protected function afterSave(): void
    {
        $bill = $this->record;

        if (!$bill->payment_status) {
            return;
        }

        foreach ($bill->saleMedicine as $item) {
            $medicine = $item->medicine;

            if (!$medicine) {
                continue;
            }

            // Old quantity from DB
            $originalQty = (int) $item->getOriginal('sale_quantity');

            // New quantity from form
            $newQty = (int) $item->sale_quantity;

            // Difference
            $diff = $newQty - $originalQty;

            // Only update if changed
            if ($diff !== 0) {
                $medicine->decrement('available_quantity', $diff);
            }
        }
    }
}
