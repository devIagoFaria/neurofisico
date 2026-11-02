<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Appointment;
use App\Models\MedicineBill;
use App\Models\Prescription;
use App\Models\SaleMedicine;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use App\Models\PrescriptionMedicineModal;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\Appointments\AppointmentResource;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EditAppointmentsPrescription extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = AppointmentResource::class;
    protected string $view = 'filament.resources.appointments.pages.edit-appointments-prescription';
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('view', ['record' => $this->record->appointment_id])),
        ];
    }
    public array $data = [];
    public Appointment $appointment;
    public ?Prescription $record = null;
    public function mount(int|string $record): void
    {
        $medicinesData = $this->record->getMedicine->map(function ($item) {
            return [
                'medicine_id' => $item->medicine,
                'dosage' => $item->dosage,
                'day' => $item->day,
                'time' => $item->time,
                'dose_interval' => $item->dose_interval,
                'comment' => $item->comment ?? null,
            ];
        })->toArray();

        $this->form->fill([
            ...$this->record->toArray(),
            'prescriptions_medicines' => $medicinesData,
        ]);
    }
    public function getTitle(): string|Htmlable
    {
        return __('messages.prescription.edit_prescription');
    }
    public function getHeading(): string
    {
        return __('messages.prescription.edit_prescription');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('health_insurance')
                                    ->label(__('messages.prescription.health_insurance') . ':')
                                    ->placeholder(__('messages.prescription.health_insurance'))
                                    ->columnSpan(1),
                                TextInput::make('low_income')
                                    ->label(__('messages.prescription.low_income') . ':')
                                    ->placeholder(__('messages.prescription.low_income'))
                                    ->columnSpan(1),
                                TextInput::make('reference')
                                    ->label(__('messages.prescription.reference') . ':')
                                    ->placeholder(__('messages.prescription.reference'))
                                    ->columnSpan(1),
                                Toggle::make('status')
                                    ->label(__('messages.common.status') . ':')
                                    ->default(true)
                                    ->inline(false),
                            ])->columnSpanFull(),
                    ]),
                Section::make(__('messages.medicine.medicine'))
                    ->headerActions([
                        Action::make('newMedicine')
                            ->label(__('messages.medicine.new_medicine'))
                            ->color('primary')
                            ->modalHeading(__('messages.prescription.new_medicine'))
                            ->modalWidth('4xl')
                            ->modalSubmitActionLabel(__('messages.common.save'))
                            ->modalCancelActionLabel(__('messages.common.cancel'))
                            ->form([
                                Grid::make(2)->schema([
                                    Hidden::make('quantity')
                                        ->default(fn ($record) => $record?->quantity ?? 0),
                                    Hidden::make('available_quantity')
                                        ->default(fn ($record) => $record?->available_quantity ?? 0),
                                    TextInput::make('name')
                                        ->label(__('messages.medicine.medicine'))
                                        ->placeholder(__('messages.medicine.medicine'))
                                        ->required()
                                        ->minLength(2)
                                        ->maxLength(255),

                                    Select::make('category_id')
                                        ->label(__('messages.medicine.category'))
                                        ->placeholder(__('messages.medicine.category'))
                                        ->options(fn () => Category::where('is_active', true)->pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),

                                    Select::make('brand_id')
                                        ->label(__('messages.medicine.brand'))
                                        ->placeholder(__('messages.medicine.brand'))
                                        ->options(fn () => Brand::pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),

                                    TextInput::make('salt_composition')
                                        ->label(__('messages.medicine.salt_composition'))
                                        ->placeholder(__('messages.medicine.salt_composition'))
                                        ->required(),

                                    TextInput::make('buying_price')
                                        ->label(__('messages.medicine.buying_price'))
                                        ->placeholder(__('messages.medicine.buying_price'))
                                        ->numeric()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->required(),

                                    TextInput::make('selling_price')
                                        ->label(__('messages.medicine.selling_price'))
                                        ->placeholder(__('messages.medicine.selling_price'))
                                        ->numeric()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->required(),

                                    Textarea::make('side_effects')
                                        ->label(__('messages.medicine.side_effects'))
                                        ->placeholder(__('messages.medicine.side_effects'))
                                        ->rows(4)
                                        ->columnSpanFull(),

                                    Textarea::make('description')
                                        ->label(__('messages.medicine.description'))
                                        ->placeholder(__('messages.medicine.description'))
                                        ->rows(4)
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->action(function (array $data) {
                                Medicine::create($data);

                                Notification::make()
                                    ->success()
                                    ->title(__('messages.flash.medicine_create'))
                                    ->send();
                            }),
                    ])
                    ->schema([
                        static::getItemsRepeater(),
                    ]),
                Section::make(__('messages.prescription.physical_information'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('high_blood_pressure')
                                    ->label(__('messages.prescription.high_blood_pressure') . ':')
                                    ->placeholder(__('messages.prescription.high_blood_pressure')),

                                TextInput::make('food_allergies')
                                    ->label(__('messages.prescription.food_allergies') . ':')
                                    ->placeholder(__('messages.prescription.food_allergies')),

                                TextInput::make('tendency_bleed')
                                    ->label(__('messages.prescription.tendency_bleed') . ':')
                                    ->placeholder(__('messages.prescription.tendency_bleed')),

                                TextInput::make('heart_disease')
                                    ->label(__('messages.prescription.heart_disease') . ':')
                                    ->placeholder(__('messages.prescription.heart_disease')),

                                TextInput::make('diabetic')
                                    ->label(__('messages.prescription.diabetic') . ':')
                                    ->placeholder(__('messages.prescription.diabetic')),

                                TextInput::make('medical_history')
                                    ->label(__('messages.prescription.medical_history') . ':')
                                    ->placeholder(__('messages.prescription.medical_history')),

                                TextInput::make('female_pregnancy')
                                    ->label(__('messages.prescription.female_pregnancy') . ':')
                                    ->placeholder(__('messages.prescription.female_pregnancy')),

                                TextInput::make('breast_feeding')
                                    ->label(__('messages.prescription.breast_feeding') . ':')
                                    ->placeholder(__('messages.prescription.breast_feeding')),

                                TextInput::make('current_medication')
                                    ->label(__('messages.prescription.current_medication') . ':')
                                    ->placeholder(__('messages.prescription.current_medication')),

                                TextInput::make('surgery')
                                    ->label(__('messages.prescription.surgery') . ':')
                                    ->placeholder(__('messages.prescription.surgery')),

                                TextInput::make('accident')
                                    ->label(__('messages.prescription.accident') . ':')
                                    ->placeholder(__('messages.prescription.accident')),

                                TextInput::make('others')
                                    ->label(__('messages.prescription.others') . ':')
                                    ->placeholder(__('messages.prescription.others')),

                                TextInput::make('plus_rate')
                                    ->label(__('messages.prescription.plus_rate') . ':')
                                    ->placeholder(__('messages.prescription.plus_rate')),

                                TextInput::make('temperature')
                                    ->label(__('messages.prescription.temperature') . ':')
                                    ->placeholder(__('messages.prescription.temperature')),

                                Textarea::make('problem_description')
                                    ->label(__('messages.prescription.problem_description') . ':')
                                    ->placeholder(__('messages.prescription.problem_description'))
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make()
                    ->schema([
                        Grid::make(12)->schema([
                            Textarea::make('test')
                                ->label(__('messages.prescription.test') . ':')
                                ->placeholder(__('messages.prescription.test'))
                                ->rows(5)
                                ->columnSpanFull(),

                            Textarea::make('advice')
                                ->label(__('messages.prescription.advice') . ':')
                                ->placeholder(__('messages.prescription.advice'))
                                ->rows(5)
                                ->columnSpanFull(),

                            Grid::make(12)
                                ->schema([
                                    TextInput::make('next_visit_qty')
                                        ->label(__('messages.prescription.next_visit') . ':')
                                        ->placeholder('1')
                                        ->numeric()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->minValue(1)
                                        ->default(1)
                                        ->columnSpan(2),

                                    Select::make('next_visit_time')
                                        ->hiddenLabel()
                                        ->placeholder(__('messages.prescription.next_visit'))
                                        ->options(Prescription::TIME_ARR)
                                        ->required()
                                        ->preload()
                                        ->columnSpan(10)
                                        ->native(false)
                                        ->extraAttributes([
                                            'class' => 'lg:mt-7',
                                        ])
                                        ->columnSpan(8),
                                ])
                                ->columnSpanFull(),
                        ]),
                    ])
            ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('update')
                ->label(__('messages.common.save'))
                ->action('update'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('prescriptions_medicines')
            ->hiddenLabel()
            ->addActionLabel(__('messages.common.add'))
            ->reorderable(false)
            ->columns(7)
            ->schema([
                Select::make('medicine_id')
                    ->label(__('messages.medicines'))
                    ->options(fn () => Medicine::where('available_quantity', '>', 0)->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->live()
                    ->columnSpan(1),

                TextInput::make('dosage')
                    ->label(__('messages.medicine.dosage'))
                    ->placeholder(__('messages.medicine.dosage'))
                    ->columnSpan(1),

                Select::make('day')
                    ->label(__('messages.prescription.duration'))
                    ->options(Prescription::DOSE_DURATION)
                    ->required()
                    ->searchable()
                    ->live()
                    ->columnSpan(1),

                Select::make('time')
                    ->label(__('messages.prescription.time'))
                    ->options(Prescription::MEAL_ARR)
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->helperText(function (Get $get) {
                        $medicineId = $get('medicine_id');

                        if (! $medicineId) {
                            return null;
                        }

                        $medicine = Medicine::find($medicineId);

                        return new \Illuminate\Support\HtmlString(
                            '<span class="text-success">'
                            . __('messages.prescription.total_quantity')
                            . ': '
                            . ($medicine->available_quantity ?? 0)
                            . '</span>'
                        );
                    })
                    ->columnSpan(1),

                Select::make('dose_interval')
                    ->label(__('messages.medicine_bills.dose_interval'))
                    ->options(Prescription::DOSE_INTERVAL)
                    ->required()
                    ->searchable()
                    ->live()
                    ->columnSpan(1),

                Textarea::make('comment')
                    ->label(__('messages.prescription.comment'))
                    ->rows(1)
                    ->columnSpan(2),
            ])
            ->defaultItems(1);
    }

    public function update(): void
    {
        $data = $this->form->getState();      // equals $this->data
        $data['status'] = ! empty($data['status']) ? 1 : 0;

        $prescription = $this->record;
        $medicines = collect($data['prescriptions_medicines'] ?? []);

        if ($medicines->pluck('medicine_id')->duplicates()->isNotEmpty()) {
            Notification::make()
                ->danger()
                ->title(__('messages.prescription.not_add_duplicate_medicines'))
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            // 1. Update prescription main fields
            $prescription->update($data);

            // 2. Restore old stock
            foreach ($prescription->getMedicine as $oldItem) {
                $qty = $oldItem->day * $oldItem->dose_interval;
                Medicine::where('id', $oldItem->medicine)
                    ->increment('available_quantity', $qty);
            }

            // 3. Remove old relations
            $prescription->getMedicine()->delete();
            SaleMedicine::whereHas('medicineBill', function ($q) use ($prescription) {
                $q->where('model_id', $prescription->id)
                ->where('model_type', Prescription::class);
            })->delete();

            MedicineBill::where('model_id', $prescription->id)
                ->where('model_type', Prescription::class)
                ->delete();

            // 4. Create new bill
            $medicineBill = MedicineBill::create([
                'bill_number'    => 'BIL' . generateUniqueBillNumber(),
                'patient_id'     => $prescription->patient_id,
                'doctor_id'      => $prescription->doctor_id,
                'model_type'     => Prescription::class,
                'model_id'       => $prescription->id,
                'bill_date'      => now(),
                'payment_status' => MedicineBill::UNPAID,
                'discount'       => 0,
                'tax_amount'     => 0,
                'payment_type'   => 0,
                'net_amount'     => 0,
                'total'          => 0,
            ]);

            $totalAmount = 0;

            // 5. Recreate medicines, sales, adjust stock
            foreach ($medicines as $item) {
                $medicine = Medicine::with('purchasedMedicines')->findOrFail($item['medicine_id']);
                $qty = (int) $item['day'] * (int) $item['dose_interval'];

                if ($medicine->available_quantity < $qty) {
                    throw new \Exception(
                        __('messages.prescription.available_quantity_of')
                        . $medicine->name . ' '
                        . __('messages.prescription.is') . ' '
                        . $medicine->available_quantity
                    );
                }

                PrescriptionMedicineModal::create([
                    'prescription_id' => $prescription->id,
                    'medicine'        => $medicine->id,
                    'dosage'          => $item['dosage'],
                    'day'             => $item['day'],
                    'time'            => $item['time'],
                    'dose_interval'   => $item['dose_interval'],
                    'comment'         => $item['comment'] ?? null,
                ]);

                $purchase = $medicine->purchasedMedicines()
                    ->whereNotNull('expiry_date')
                    ->orderBy('expiry_date')
                    ->first();

                SaleMedicine::create([
                    'medicine_bill_id' => $medicineBill->id,
                    'medicine_id'      => $medicine->id,
                    'sale_quantity'    => $qty,
                    'sale_price'       => $medicine->selling_price,
                    'expiry_date'      => $purchase?->expiry_date,
                    'tax'              => 0,
                    'amount'           => $qty * $medicine->selling_price,
                ]);

                $medicine->decrement('available_quantity', $qty);
                $totalAmount += $qty * $medicine->selling_price;
            }

            // 6. Update bill totals
            $medicineBill->update([
                'net_amount' => $totalAmount,
                'total'      => $totalAmount,
            ]);

            DB::commit();

            Notification::make()
                ->success()
                ->title(__('messages.prescription.prescription_updated'))
                ->send();

            $this->redirect(
                AppointmentResource::getUrl('view', [
                    'record' => $prescription->appointment_id,
                ])
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title($e->getMessage())
                ->send();
        }
    }

}
