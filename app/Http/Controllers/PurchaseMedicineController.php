<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Repositories\PurchaseMedicineRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PurchaseMedicineController extends AppBaseController
{
    private $purchaseMedicineRepository;
    public function __construct(PurchaseMedicineRepository $purchaseMedicineRepo)
    {
        $this->purchaseMedicineRepository = $purchaseMedicineRepo;
    }
        public function store($input)
    {

        try {
            DB::beginTransaction();

            $this->purchaseMedicineRepository->store($input);
            DB::commit();

            return ;

        } catch (Exception $e) {

            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

}
