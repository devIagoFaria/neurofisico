<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Repositories\PrescriptionRepository;

class PrescriptionController extends AppBaseController
{
    private $prescriptionRepository;
    public function __construct(
        PrescriptionRepository $prescriptionRepo,
    ) {
        $this->prescriptionRepository = $prescriptionRepo;
    }
    public function convertToPDF($id): \Illuminate\Http\Response
    {
        $data = $this->prescriptionRepository->getSettingList();
        $prescription = $this->prescriptionRepository->getData($id);
        $medicines = $this->prescriptionRepository->getMedicineData($id);

        $pdf = Pdf::loadView('prescriptions.prescription_pdf', compact('prescription', 'medicines', 'data'));

        return $pdf->stream(
            $prescription['prescription']->patient->user->full_name
            . '-' .
            $prescription['prescription']->id
        );
    }
}
