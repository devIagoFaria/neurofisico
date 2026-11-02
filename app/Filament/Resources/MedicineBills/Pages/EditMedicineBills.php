<?php

namespace App\Filament\Resources\MedicineBills\Pages;

use App\Models\Medicine;
use App\Models\MedicineBill;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\MedicineBills\MedicineBillsResource;

class EditMedicineBills extends EditRecord
{
    protected static string $resource = MedicineBillsResource::class;
    protected array $originalQuantities = [];

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $medicineBill = MedicineBill::with('saleMedicine.medicine.category')->find($data['id']);
        if ($medicineBill) {
            $saleMedicine = $medicineBill->saleMedicine->first();
            if ($saleMedicine && $saleMedicine->medicine && $saleMedicine->medicine->category) {
                $data['category_id'] = $saleMedicine->medicine->category->id;
            }
        }

        return $data;
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

    protected function beforeFill(): void
    {
        foreach ($this->record->saleMedicine as $item) {
            $this->originalQuantities[$item->id] = (int) $item->sale_quantity;
        }
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

            $oldQty = $this->originalQuantities[$item->id] ?? 0;
            $newQty = (int) $item->sale_quantity;

            $diff = $newQty - $oldQty;

            if ($diff > 0) {
                $medicine->decrement('available_quantity', $diff);
            }

            if ($diff < 0) {
                $medicine->increment('available_quantity', abs($diff));
            }
        }
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.medicine_bill_update');
    }
}
