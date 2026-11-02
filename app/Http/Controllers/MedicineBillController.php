<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineBill;
use App\Models\SaleMedicine;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use App\Repositories\PrescriptionRepository;

class MedicineBillController extends AppBaseController
{
    private $prescriptionRepository;
    public function __construct(PrescriptionRepository $prescriptionRepo) {
        $this->prescriptionRepository = $prescriptionRepo;
    }
    public function convertToPDF($id): Response
    {
        if (app()->getLocale() == 'zh') {
            app()->setLocale('en');
        }
        $data = $this->prescriptionRepository->getSettingList();

        // Get the image contents
        $imageData = Http::get($data['logo'])->body();
        $imageType = pathinfo($data['logo'], PATHINFO_EXTENSION);
        $base64Image = 'data:image/'.$imageType.';base64,'.base64_encode($imageData);

        $data['logo'] = $base64Image;
        $medicineBill = MedicineBill::with(['saleMedicine.medicine'])->where('id', $id)->first();

        $pdf = Pdf::loadView('medicine-bills.medicine_bill_pdf', compact('medicineBill', 'data'));

        return $pdf->stream('medicine-bill.pdf');
    }
    public function store(array $input)
    {
        if (empty($input['saleMedicine'] ?? [])) {
            return ['error' => __('messages.medicine_bills.medicine_not_selected')];
        }

        // Proper duplicate check using medicine_id from repeater
        $arr = collect($input['saleMedicine'])->pluck('medicine_id');
        $duplicateIds = $arr->duplicates();

        $input['payment_status'] = isset($input['payment_status']) ? 1 : 0;

        // use repeater data structure
        foreach ($input['saleMedicine'] as $key => $item) {
            $medicine = Medicine::find($item['medicine_id']);

            if (!empty($duplicateIds)) {
                foreach ($duplicateIds as $dupKey => $dupValue) {
                    $dupMedicine = Medicine::find($dupValue);
                    $data['error'] = __('messages.medicine_bills.duplicate_medicine');
                    return $data;
                }
            }

            $qty = $item['sale_quantity'];
            if ($medicine->available_quantity < $qty) {
                $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;
                $data['error'] = __('messages.medicine_bills.available_quantity').' '.$medicine->name.' '.__('messages.medicine_bills.is').' '.$available;
                return $data;
            }
        }

        // Create main MedicineBill
        $medicineBill = MedicineBill::create([
            'bill_number' => 'BIL'.generateUniqueBillNumber(),
            'patient_id' => $input['patient_id'],
            'net_amount' => $input['net_amount'],
            'discount' => $input['discount'],
            'payment_status' => $input['payment_status'],
            'payment_type' => $input['payment_type'],
            'note' => $input['note'],
            'total' => $input['total'],
            'tax_amount' => $input['tax_amount'],
            'payment_note' => $input['payment_note'],
            'model_type' => MedicineBill::class,
            'model_id' => 1,
            'bill_date' => $input['bill_date'],
        ]);

        $medicineBill->update([
            'model_id' => $medicineBill->id,
        ]);

        if (!empty($input['saleMedicine'])) {
            foreach ($input['saleMedicine'] as $key => $item) {
                $medicine = Medicine::find($item['medicine_id']);
                $tax = $item['tax'] == null ? 0 : $item['tax'];

                SaleMedicine::create([
                    'medicine_bill_id' => $medicineBill->id,
                    'medicine_id' => $medicine->id,
                    'sale_price' => $item['sale_price'],
                    'expiry_date' => $item['expiry_date'],
                    'sale_quantity' => $item['sale_quantity'],
                    'tax' => $tax,
                    'amount' => $item['amount'],
                ]);

                if ($input['payment_status'] == 1) {
                    $medicine->update([
                        'available_quantity' => $medicine->available_quantity - $item['sale_quantity'],
                    ]);
                }
            }

            return ['success' => true, 'medicine_bill_id' => $medicineBill->id];
        }
    }
}
