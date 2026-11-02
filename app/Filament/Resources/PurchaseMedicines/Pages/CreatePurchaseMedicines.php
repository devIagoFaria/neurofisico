<?php

namespace App\Filament\Resources\PurchaseMedicines\Pages;

use Exception;
use App\Models\PurchaseMedicine;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Http\Controllers\PurchaseMedicineController;
use App\Filament\Resources\PurchaseMedicines\PurchaseMedicinesResource;

class CreatePurchaseMedicines extends CreateRecord
{
    protected static string $resource = PurchaseMedicinesResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.purchase_medicine.purchased_medicine_success');
    }

    protected function handleRecordCreation(array $input): Model
    {
        try {
            $input['total'] = (float) ($input['total'] ?? 0);
            $input['discount'] = (float) ($input['discount'] ?? 0);
            $input['tax'] = (float) ($input['tax'] ?? 0);
            $input['net_amount'] = (float) ($input['net_amount'] ?? 0);

            $purchaseMedicineController = app(PurchaseMedicineController::class);
            $data = $purchaseMedicineController->store($input);

            if (is_array($data) && array_key_exists('error', $data)) {
                Notification::make()
                    ->title($data['error'])
                    ->danger()
                    ->send();
                $this->halt();
            }

            $medicinePurchase = [
                'purchase_no' => '1234',
            ];

            $purchaseMedicine = new PurchaseMedicine($medicinePurchase);

            return $purchaseMedicine;
        } catch (Exception $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            $this->halt();
        }
    }

}
