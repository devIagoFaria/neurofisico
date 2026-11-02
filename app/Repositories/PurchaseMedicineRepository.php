<?php

namespace App\Repositories;

use Exception;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Support\Arr;
use App\Models\PurchaseMedicine;
use App\Models\PurchasedMedicine;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class AccountantRepository
 *
 * @version February 17, 2020, 5:34 am UTC
 */
class PurchaseMedicineRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_numeber',
        'purchase_date',
        'bill_number',
        'supplier_name',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return PurchaseMedicine::class;
    }

    public function getMedicine()
    {
        $data['medicines'] = Medicine::all()->pluck('name', 'id')->toArray();

        return $data;
    }

    public function getMedicineList()
    {
        $result = Medicine::all()->pluck('name', 'id')->toArray();

        $medicines = [];
        foreach ($result as $key => $item) {
            $medicines[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $medicines;
    }

    public function getCategoryList()
    {
        $result = Category::all()->pluck('name', 'id')->toArray();

        $category = [];
        foreach ($result as $key => $item) {
            $medicines[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $category;
    }

    public function getCategory()
    {
        $data['categories'] = Category::all()->pluck('name', 'id')->toArray();

        return $data;
    }

    /**
     * @param  bool  $mail
     */
    public function store(array $input)
    {
        try {
            DB::beginTransaction();
            $purchaseMedicineArray = Arr::only($input, $this->model->getFillable());
            $purchaseMedicine = PurchaseMedicine::create($purchaseMedicineArray);

            $structuredMedicines = [
                'medicine' => [],
                'lot_no' => [],
                'expiry_date' => [],
                'sale_price' => [],
                'purchase_price' => [],
                'quantity' => [],
                'tax' => [],
                'amount' => [],

            ];

            foreach ($input['purchasedMedcines'] as $medicineData) {
                $structuredMedicines['medicine'][] = $medicineData['medicine_id'];
                $structuredMedicines['lot_no'][] = $medicineData['lot_no'];
                $structuredMedicines['expiry_date'][] = $medicineData['expiry_date'];
                $structuredMedicines['sale_price'][] = $medicineData['sale_price'];
                $structuredMedicines['purchase_price'][] = $medicineData['purchase_price'];
                $structuredMedicines['quantity'][] = $medicineData['quantity'];
                $structuredMedicines['tax'][] = $medicineData['tax'];
                $structuredMedicines['amount'][] = $medicineData['amount'];
            }

            foreach ($structuredMedicines['medicine'] as $key => $value) {

                $purchasedMedicineArray = [
                    'purchase_medicines_id' => $purchaseMedicine->id,
                    'medicine_id' => $structuredMedicines['medicine'][$key],
                    'lot_no' => $structuredMedicines['lot_no'][$key],
                    'tax' => $structuredMedicines['tax'][$key],
                    'expiry_date' => $structuredMedicines['expiry_date'][$key],
                    'quantity' => $structuredMedicines['quantity'][$key],
                    'amount' => $structuredMedicines['amount'][$key],
                ];

                PurchasedMedicine::create($purchasedMedicineArray);
                $medicine = Medicine::find($structuredMedicines['medicine'][$key]);
                $medicineQtyArray = [
                    'quantity' => $structuredMedicines['quantity'][$key] + $medicine->quantity,
                    'available_quantity' => $structuredMedicines['quantity'][$key] + $medicine->available_quantity,
                ];
                $medicine->update($medicineQtyArray);
            }
            DB::commit();

            return $purchaseMedicine;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
