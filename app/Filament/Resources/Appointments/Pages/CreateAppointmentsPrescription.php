<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicineBill;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicineModal;
use App\Models\SaleMedicine;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CreateAppointmentsPrescription extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AppointmentResource::class;

    protected string $view = 'filament.resources.appointments.pages.create-appointments-prescription';

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('messages.prescription.new_prescription');
    }

    public array $data = [];

    public Appointment $appointment;

    public function mount(int|string $record): void
    {
        $this->appointment = Appointment::findOrFail($record);
        $this->form->fill();
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
                                    ->label(__('messages.prescription.health_insurance').':')
                                    ->placeholder(__('messages.prescription.health_insurance'))
                                    ->columnSpan(1),
                                TextInput::make('low_income')
                                    ->label(__('messages.prescription.low_income').':')
                                    ->placeholder(__('messages.prescription.low_income'))
                                    ->columnSpan(1),
                                TextInput::make('reference')
                                    ->label(__('messages.prescription.reference').':')
                                    ->placeholder(__('messages.prescription.reference'))
                                    ->columnSpan(1),
                                Toggle::make('status')
                                    ->label(__('messages.web.status').':')
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
                                    ->label(__('messages.prescription.high_blood_pressure').':')
                                    ->placeholder(__('messages.prescription.high_blood_pressure')),

                                TextInput::make('food_allergies')
                                    ->label(__('messages.prescription.food_allergies').':')
                                    ->placeholder(__('messages.prescription.food_allergies')),

                                TextInput::make('tendency_bleed')
                                    ->label(__('messages.prescription.tendency_bleed').':')
                                    ->placeholder(__('messages.prescription.tendency_bleed')),

                                TextInput::make('heart_disease')
                                    ->label(__('messages.prescription.heart_disease').':')
                                    ->placeholder(__('messages.prescription.heart_disease')),

                                TextInput::make('diabetic')
                                    ->label(__('messages.prescription.diabetic').':')
                                    ->placeholder(__('messages.prescription.diabetic')),

                                TextInput::make('medical_history')
                                    ->label(__('messages.prescription.medical_history').':')
                                    ->placeholder(__('messages.prescription.medical_history')),

                                TextInput::make('female_pregnancy')
                                    ->label(__('messages.prescription.female_pregnancy').':')
                                    ->placeholder(__('messages.prescription.female_pregnancy')),

                                TextInput::make('breast_feeding')
                                    ->label(__('messages.prescription.breast_feeding').':')
                                    ->placeholder(__('messages.prescription.breast_feeding')),

                                TextInput::make('current_medication')
                                    ->label(__('messages.prescription.current_medication').':')
                                    ->placeholder(__('messages.prescription.current_medication')),

                                TextInput::make('surgery')
                                    ->label(__('messages.prescription.surgery').':')
                                    ->placeholder(__('messages.prescription.surgery')),

                                TextInput::make('accident')
                                    ->label(__('messages.prescription.accident').':')
                                    ->placeholder(__('messages.prescription.accident')),

                                TextInput::make('others')
                                    ->label(__('messages.prescription.others').':')
                                    ->placeholder(__('messages.prescription.others')),

                                TextInput::make('plus_rate')
                                    ->label(__('messages.prescription.plus_rate').':')
                                    ->placeholder(__('messages.prescription.plus_rate')),

                                TextInput::make('temperature')
                                    ->label(__('messages.prescription.temperature').':')
                                    ->placeholder(__('messages.prescription.temperature')),

                                Textarea::make('problem_description')
                                    ->label(__('messages.prescription.problem_description').':')
                                    ->placeholder(__('messages.prescription.problem_description'))
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make()
                    ->schema([
                        Grid::make(12)->schema([
                            Textarea::make('test')
                                ->label(__('messages.prescription.test').':')
                                ->placeholder(__('messages.prescription.test'))
                                ->rows(5)
                                ->columnSpanFull(),

                            Textarea::make('advice')
                                ->label(__('messages.prescription.advice').':')
                                ->placeholder(__('messages.prescription.advice'))
                                ->rows(5)
                                ->columnSpanFull(),

                            Grid::make(12)
                                ->schema([
                                    TextInput::make('next_visit_qty')
                                        ->label(__('messages.prescription.next_visit').':')
                                        ->numeric()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->minValue(1)
                                        ->default(1)
                                        ->columnSpan(2),

                                    Select::make('next_visit_time')
                                        ->hiddenLabel()
                                        ->placeholder(__('messages.prescription.next_visit'))
                                        ->options([
                                            Prescription::DAYS => __('messages.admin_dashboard.day'),
                                            Prescription::MONTH => __('messages.admin_dashboard.month'),
                                            Prescription::YEAR => __('messages.common.years'),
                                        ])
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
                    ]),
            ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.common.save'))
                ->action('save'),
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
                    ->options([
                        Prescription::ONE_DAY => __('messages.prescription.one_day_only'),
                        Prescription::THREE_DAY => __('messages.prescription.for_three_days'),
                        Prescription::ONE_WEEK => __('messages.prescription.for_one_week'),
                        Prescription::TWO_WEEK => __('messages.prescription.for_two_weeks'),
                        Prescription::ONE_MONTH => __('messages.prescription.for_one_month'),
                    ])
                    ->required()
                    ->searchable()
                    ->live()
                    ->columnSpan(1),

                Select::make('time')
                    ->label(__('messages.prescription.time'))
                    ->options([
                        Prescription::AFETR_MEAL => __('messages.prescription.after_meal'),
                        Prescription::BEFORE_MEAL => __('messages.prescription.before_meal'),
                    ])
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
                            .__('messages.prescription.total_quantity')
                            .': '
                            .($medicine->available_quantity ?? 0)
                            .'</span>'
                        );
                    })
                    ->columnSpan(1),

                Select::make('dose_interval')
                    ->label(__('messages.medicine_bills.dose_interval'))
                    ->options([
                        Prescription::ONE_TIME => __('messages.prescription.every_morning'),
                        Prescription::TWO_TIME => __('messages.prescription.every_morning_evening'),
                        Prescription::THREE_TIME => __('messages.prescription.three_times_a_day'),
                        Prescription::FOUR_TIME => __('messages.prescription.four_times_a_day'),
                    ])
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

    public function save(): void
    {
        $data = $this->form->getState();

        $data['status'] = ! empty($data['status']) ? 1 : 0;

        $medicines = collect($data['prescriptions_medicines'] ?? []);

        $duplicate = $medicines->pluck('medicine_id')->duplicates();
        if ($duplicate->isNotEmpty()) {
            Notification::make()
                ->danger()
                ->title(__('messages.prescription.not_add_duplicate_medicines'))
                ->send();

            return;
        }

        foreach ($medicines as $item) {
            $medicine = Medicine::find($item['medicine_id']);

            $requiredQty = $item['day'] * $item['dose_interval'];
            $availableQty = $medicine->available_quantity ?? 0;

            if ($availableQty < $requiredQty) {
                Notification::make()
                    ->danger()
                    ->title(
                        __('messages.prescription.available_quantity_of')
                        .$medicine->name.' '
                        .__('messages.prescription.is').' '
                        .$availableQty
                    )
                    ->send();

                return;
            }
        }

        try {
            DB::beginTransaction();

            $prescription = Prescription::create([
                ...$data,
                'appointment_id' => $this->appointment->id,
                'patient_id' => auth()->user()->hasRole('patient')
                    ? Patient::where('user_id', auth()->id())->value('id')
                    : $this->appointment->patient_id,
                'doctor_id' => auth()->user()->hasRole('doctor')
                    ? Doctor::where('user_id', auth()->id())->value('id')
                    : $this->appointment->doctor_id,
            ]);

            $totalAmount = 0;

            if ($medicines->isNotEmpty()) {
                $medicineBill = MedicineBill::create([
                    'bill_number' => 'BIL'.generateUniqueBillNumber(),
                    'patient_id' => $prescription->patient_id,
                    'doctor_id' => $prescription->doctor_id,
                    'model_type' => Prescription::class,
                    'model_id' => $prescription->id,
                    'bill_date' => now(),
                    'payment_status' => MedicineBill::UNPAID,
                    'discount' => 0,
                    'net_amount' => 0,
                    'total' => 0,
                    'tax_amount' => 0,
                    'payment_type' => 0,
                ]);

                foreach ($medicines as $item) {
                    PrescriptionMedicineModal::create([
                        'prescription_id' => $prescription->id,
                        'medicine' => $item['medicine_id'],
                        'dosage' => $item['dosage'],
                        'day' => $item['day'],
                        'time' => $item['time'],
                        'dose_interval' => $item['dose_interval'],
                        'comment' => $item['comment'] ?? null,
                    ]);

                    $medicine = Medicine::with(['purchasedMedicines'])->find($item['medicine_id']);
                    $qty = $item['day'] * $item['dose_interval'];
                    $purchase = $medicine->purchasedMedicines()
                        ->whereNotNull('expiry_date')
                        ->latest('expiry_date')
                        ->first();

                    $saleMedicine = SaleMedicine::create([
                        'medicine_bill_id' => $medicineBill->id,
                        'medicine_id' => $medicine->id,
                        'sale_quantity' => $qty,
                        'sale_price' => $medicine->selling_price,
                        'expiry_date' => $purchase?->expiry_date,
                        'tax' => 0,
                        'amount' => 0,
                    ]);

                    $medicine->decrement('available_quantity', $qty);
                    $totalAmount += $qty * $medicine->selling_price;
                }

                $medicineBill->update([
                    'net_amount' => $totalAmount,
                    'total' => $totalAmount,
                ]);

                $saleMedicine->update([
                    'amount' => $totalAmount,
                ]);

            }

            DB::commit();

            Notification::make()
                ->success()
                ->title(__('messages.prescription.prescription_saved'))
                ->send();

            $this->redirect(AppointmentResource::getUrl('view', [
                'record' => $prescription->appointment_id,
            ]));

        } catch (\Throwable $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
