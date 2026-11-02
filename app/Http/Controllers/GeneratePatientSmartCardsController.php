<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class GeneratePatientSmartCardsController extends Controller
{
    public function smartCardPdf($id)
    {
        $data = Patient::with('smartPatientCard', 'user', 'address')->findOrFail($id);

        $logo = Setting::where('key', 'logo')->pluck('value')->first();
        $clinic_name = Setting::where('key', 'clinic_name')->pluck('value')->first();
        $address_one = Setting::where('key', 'address_one')->pluck('value')->first();

        $pdf = PDF::loadView('smart_card_pdf.smart_card_pdf', ['data' => $data, 'logo' => $logo, 'clinic_name' => $clinic_name, 'address_one' => $address_one]);

        return $pdf->download('PatientSmartCard.pdf');
    }
}
