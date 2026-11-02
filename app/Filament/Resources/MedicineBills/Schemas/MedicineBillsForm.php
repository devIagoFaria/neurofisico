<?php

namespace App\Filament\Resources\MedicineBills\Schemas;

use Carbon\Carbon;
use App\Models\Patient;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBill;
use Filament\Schemas\Schema;
use App\Models\PurchasedMedicine;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class MedicineBillsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('patient_id')
                            ->label(__('messages.web.patient_name') . ':')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->columnSpan(1)
                            ->options(Patient::with('user')->orderBy('id', 'desc')->get()->pluck('user.full_name', 'id'))
                            ->placeholder(__('messages.web.patient_name')),
                        DateTimePicker::make('bill_date')
                            ->seconds(false)
                            ->native(false)
                            ->default(now())
                            ->columnSpan(1)
                            ->label(__('messages.medicine_bills.bill_date') . ':')
                            ->required(),
                        Toggle::make('payment_status')
                            ->label(__('messages.medicine_bills.payment_status') . ':')
                            ->inline(false)
                            ->columnSpan(1)
                            ->default(true),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Grid::make('1')
                    ->schema([
                        static::getItemsRepeater(),
                    ])->columnSpanFull(),

                Grid::make(12)
                    ->schema([
                        Textarea::make('note')
                            ->label(__('messages.visit.notes') . ':')
                            ->placeholder(__('messages.visit.notes'))
                            ->rows(4)
                            ->columnSpan(6),

                        Group::make()
                            ->columnSpan(1),

                        Group::make()
                            ->schema([

                                TextInput::make('total')
                                    ->label(__('messages.purchase_medicine.total') . ':')
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                    ->minValue(1)
                                    ->required()
                                    ->readOnly()
                                    ->inlineLabel(true)
                                    ->dehydrated()
                                    ->reactive()
                                    ->default(0.00)
                                    ->columnSpan(5)
                                    ->live(),

                                TextInput::make('discount')
                                    ->label(__('messages.purchase_medicine.discount') . ':')
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                    ->minValue(0)
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        if ($state > 100) {
                                            $set('discount', 100);
                                        }
                                        self::updateTotal($get, $set);
                                    })
                                    ->inlineLabel(true)
                                    ->default(0.00)
                                    ->required()
                                    ->columnSpan(5)
                                    ->reactive(),

                                TextInput::make('tax_amount')
                                    ->label(__('messages.purchase_medicine.tax_amount') . ':')
                                    ->minValue(1)
                                    ->inlineLabel(true)
                                    ->default(0.00)
                                    ->readOnly()
                                    ->columnSpan(5),

                                TextInput::make('net_amount')
                                    ->label(__('messages.purchase_medicine.net_amount') . ':')
                                    ->required()
                                    ->validationAttribute(__('messages.purchase_medicine.net_amount'))
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                    ->inlineLabel(true)
                                    ->dehydrated()
                                    ->reactive()
                                    ->readOnly()
                                    ->default(0.00)
                                    ->columnSpan(5),

                                Select::make('payment_type')
                                    ->label(__('messages.purchase_medicine.payment_mode') . ':')
                                    ->placeholder(__('messages.purchase_medicine.payment_mode'))
                                    ->options([
                                        '1' => __('messages.payment_method.cash'),
                                        '2' => __('messages.payment_method.cheque'),
                                        '3' => __('messages.payment_method.other'),
                                    ])
                                    ->inlineLabel(true)
                                    ->required(true)
                                    ->native(false)
                                    ->columnSpan(5),

                                Textarea::make('payment_note')
                                    ->label(__('messages.purchase_medicine.payment_note') . ':')
                                    ->placeholder(__('messages.purchase_medicine.payment_note'))
                                    ->rows(3),
                            ])
                            ->columnSpan(5),
                        Hidden::make('purchase_no')
                            ->default(generateUniquePurchaseNumber())
                            ->dehydrated(),
                        Hidden::make('bill_number')
                            ->default('BIL' . generateUniqueBillNumber())
                            ->dehydrated(),
                        Hidden::make('model_type')
                            ->default(MedicineBill::class)
                            ->dehydrated(),
                        Hidden::make('model_id')
                            ->default(1)
                            ->dehydrated(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('saleMedicine')
            ->relationship('saleMedicine')
            ->addActionLabel(__('messages.medicine_bills.sale_medicine'))
            ->dehydrated()
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('category_id')
                            ->label(__('messages.medicine_categories') . ':')
                            ->afterStateHydrated(function ($record, Set $set, string $operation) {
                                if (
                                    $operation === 'edit'
                                    && $record !== null
                                    && $record->medicine !== null
                                    && $record->medicine->category !== null
                                ) {
                                    $set('category_id', $record->medicine->category->id);
                                }
                            })
                            ->options(Category::pluck('name', 'id'))
                            ->placeholder(__('messages.common.select_category'))
                            ->native(false)
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('medicine_id', null);
                            })->columnSpan([
                                'md' => 2,
                                'xl' => 2,
                                '2xl' => 2,
                                '3xl' => 2,
                            ]),

                        Select::make('medicine_id')
                            ->label(__('messages.medicine.medicine') . ':')
                            ->placeholder(__('messages.medicine_bills.select_medicine'))
                            ->options(function (Get $get, $record) {
                                $categoryId = $get('category_id');
                                if ($categoryId) {
                                    return Medicine::where('category_id', $categoryId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }

                                return [];
                            })
                            ->required()
                            ->live()
                            ->reactive()
                            ->afterStateHydrated(function ($state, Set $set, string $operation, $record) {
                                if ($operation === 'edit' && $record?->medicine) {
                                    $set('quantity_suffix', $record->medicine->quantity ?? '');
                                }
                            })
                            ->afterStateUpdated(function ($state, Set $set) {
                                $medicine = Medicine::find($state);

                                $purchaseMedicine = PurchasedMedicine::where('medicine_id', $medicine->id)->latest()->first();
                                $totalPurchased = PurchasedMedicine::where('medicine_id', $medicine->id)->sum('quantity');
                                $totalSold = \App\Models\SaleMedicine::where('medicine_id', $medicine->id)->sum('sale_quantity');

                                $availableStock = $totalPurchased - $totalSold;

                                $set('sale_price', $medicine->selling_price ?? 0);
                                $set('expiry_date',$purchaseMedicine?->expiry_date ?? Carbon::now()->startOfDay()->format('Y-m-d'));
                                $set('quantity_suffix', max($availableStock, 0));
                            })
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->searchable()->columnSpan([
                                'md' => 2,
                                'xl' => 2,
                                '2xl' => 2,
                                '3xl' => 2,
                            ]),

                        DatePicker::make('expiry_date')
                            ->label(__('messages.purchase_medicine.expiry_date') . ':')
                            ->placeholder(__('messages.purchase_medicine.expiry_date'))
                            ->native(false)
                            ->minDate(function () {
                                return Carbon::now()->startOfDay()->format('Y-m-d');
                            })
                            ->validationAttribute(__('messages.purchase_medicine.expiry_date'))
                            ->required(),

                        TextInput::make('sale_price')
                            ->label(__('messages.medicine_bills.sale_price') . ':')
                            ->placeholder(__('messages.medicine_bills.sale_price'))
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->minValue(1)
                            ->required()
                            ->validationAttribute(__('messages.medicine_bills.sale_price'))
                            ->reactive()
                            ->minValue(0)
                            ->afterStateUpdated(function ($state, ?Set $set = null, $get = null) {
                                self::updateTotal($get, $set);
                            })
                            ->live()
                            ->default(0.00),

                        TextInput::make('sale_quantity')
                            ->label(__('messages.purchase_medicine.quantity') . ':')
                            ->placeholder(__('messages.purchase_medicine.quantity'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, ?Set $set = null, $get = null) {
                                self::updateTotal($get, $set);
                            })
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->required()
                            ->validationAttribute(__('messages.purchase_medicine.quantity'))
                            ->default('0')
                            ->minValue(0)
                            ->live()
                            ->suffix(function (Get $get) {
                                return ($get('quantity_suffix') ?? 0);
                            }),

                        TextInput::make('tax')
                            ->label(__('messages.purchase_medicine.tax') . ':')
                            ->placeholder(__('messages.purchase_medicine.tax'))
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->reactive()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('amount')
                            ->label(__('messages.purchase_medicine.amount') . ':')
                            ->placeholder(__('messages.purchase_medicine.amount'))
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->required()
                            ->validationAttribute(__('messages.purchase_medicine.amount'))
                            ->minValue(0)
                            ->readOnly()
                            ->default(0),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'lg' => 4,
                        'xl' => 6,
                        '2xl' => 9,
                        '3xl' => 9,
                    ])
                    ->hiddenLabel(),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->required()
            ->afterStateUpdated(function ($state, Set $set, $get) {
                self::updateTotal($get, $set);
            });
    }

    public static function updateTotal($get, $set): void
    {
        $items = collect($get('saleMedicine'))->values()->toArray();

        $total = 0;
        $totalamt = 0;
        $taxamt = 0;

        foreach ($items as $index => $item) {

            $purchasePrice = (float) ($item['sale_price'] ?? 0);
            $quantity = (int) ($item['sale_quantity'] ?? 0);
            $tax = (int) ($item['tax'] ?? 0);

            $totalamt += $purchasePrice * $quantity;
            $taxamt += ($purchasePrice * $quantity) * ($tax / 100);

            $total += ($purchasePrice * $quantity) * ((100 + $tax) / 100);
        }

        $set('amount', round(((int) $get('sale_price')) * ((int) $get('sale_quantity')), 2));
        $set('tax_amount', round($taxamt, 2));
        $set('total', round($totalamt, 2));

        $totalAmount = round($total, 2);
        $discount = (float) ($get('discount') ?? 0);

        if ($discount > 0) {
            $netAmount = $totalAmount - $discount;
            $set('net_amount', round(max($netAmount, 0), 2));
        } else {
            $set('net_amount', round($totalAmount, 2));
        }
    }

}
