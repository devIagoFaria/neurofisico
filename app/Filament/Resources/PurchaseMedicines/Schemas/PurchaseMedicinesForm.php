<?php

namespace App\Filament\Resources\PurchaseMedicines\Schemas;

use Closure;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Medicine;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PurchaseMedicinesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.purchase_medicine.order_items'))
                    ->headerActions([
                        Action::make(__('messages.common.reset'))
                            ->modalHeading(__('messages.common.are_u_sure'))
                            ->requiresConfirmation()
                            ->color('danger')
                            ->action(fn(Set $set) => $set('items', [])),
                    ])
                    ->schema([
                        static::getItemsRepeater(),

                        Grid::make(12)
                            ->schema([
                                Textarea::make('note')
                                    ->label(__('messages.visit.notes')  . ':')
                                    ->placeholder(__('messages.visit.notes'))
                                    ->rows(4)
                                    ->columnSpan(6),

                                Group::make()
                                    ->columnSpan(1),

                                Group::make()
                                    ->schema([
                                        TextInput::make('total')
                                            ->label(__('messages.purchase_medicine.total')  . ':')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(0.00)
                                            ->inlineLabel()
                                            ->required()
                                            ->formatStateUsing(
                                                fn($state) =>
                                                number_format((float) $state, 2, '.', '')
                                            ),

                                        TextInput::make('discount')
                                            ->label(__('messages.purchase_medicine.discount')  . ':')
                                            ->numeric()
                                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                            ->reactive()
                                            ->inlineLabel()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0.00)
                                            ->rule(function (Get $get) {
                                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                                    [, , $grossTotal] = self::calculateTotals($get);

                                                    if ((float) $value > $grossTotal) {
                                                        $fail(__('messages.purchase_medicine.discount_cannot_exceed_total'));
                                                    }
                                                };
                                            })
                                            ->afterStateUpdated(
                                                fn($state, Set $set, $get) =>
                                                self::updateTotal($get, $set)
                                            ),

                                        TextInput::make('tax')
                                            ->label(__('messages.purchase_medicine.tax_amount')  . ':')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->inlineLabel()
                                            ->default(0.00)
                                            ->formatStateUsing(
                                                fn($state) =>
                                                number_format((float) $state, 2, '.', '')
                                            ),

                                        TextInput::make('net_amount')
                                            ->label(__('messages.purchase_medicine.net_amount')  . ':')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->inlineLabel()
                                            ->required()
                                            ->default(0.00)
                                            ->formatStateUsing(
                                                fn($state) =>
                                                number_format((float) $state, 2, '.', '')
                                            ),

                                        Select::make('payment_type')
                                            ->label(__('messages.purchase_medicine.payment_mode')  . ':')
                                            ->options([
                                                '0' => __('messages.payment_method.cash'),
                                                '1' => __('messages.payment_method.cheque'),
                                                '2' => __('messages.payment_method.other'),
                                            ])
                                            ->required()
                                            ->inlineLabel(),

                                        Textarea::make('payment_note')
                                            ->label(__('messages.purchase_medicine.payment_note')  . ':')
                                            ->rows(3),
                                    ])
                                    ->columnSpan(5),

                                Hidden::make('purchase_no')
                                    ->default(generateUniquePurchaseNumber())
                                    ->dehydrated(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('purchasedMedcines')
            ->relationship('purchasedMedcines')
            ->schema([
                Select::make('medicine_id')
                    ->label(__('messages.medicine.medicine') . ':')
                    ->placeholder(__('messages.medicine_bills.select_medicine'))
                    ->options(Medicine::pluck('name', 'id')->toArray())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $medicine = Medicine::find($state);
                        $set('sale_price', $medicine?->selling_price ?? 0);
                        $set('purchase_price', $medicine?->buying_price ?? 0);
                    })
                    ->searchable()
                    ->columnSpan([
                        'md' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                        '3xl' => 2,
                    ]),
                TextInput::make('lot_no')
                    ->label(__('messages.purchase_medicine.lot_no')  . ':')
                    ->placeholder(__('messages.purchase_medicine.lot_no'))
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        $state < 0 ? $set('lot_no', 0) : $set('lot_no', $state);
                    })
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                    ->validationAttribute(__('messages.purchase_medicine.lot_no'))
                    ->required(),
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
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->dehydrated()
                    ->default(0.00)
                    ->reactive()
                    ->formatStateUsing(
                        fn($state) =>
                        number_format((float) $state, 2, '.', '')
                    ),
                TextInput::make('purchase_price')
                    ->label(__('messages.common.purchase_price')  . ':')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->dehydrated(true)
                    ->reactive()
                    ->default(0.00)
                    ->formatStateUsing(
                        fn($state) =>
                        number_format((float) $state, 2, '.', '')
                    ),
                TextInput::make('quantity')
                    ->label(__('messages.medicine.quantity')  . ':')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0)
                    ->reactive()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                    ->afterStateUpdated(function ($state, Set $set, $get) {
                        if ($state < 0) {
                            $set('quantity', 0);
                        }
                
                        self::calculateRowAmount($set, $get);
                    }),
                TextInput::make('tax')
                    ->label(__('messages.purchase_medicine.tax')  . ':')
                    ->placeholder(__('messages.purchase_medicine.tax'))
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                    ->suffix('%')
                    ->validationAttribute(__('messages.purchase_medicine.tax'))
                    ->reactive()
                    ->minValue(0)
                    ->maxValue(100)
                    ->reactive()
                    ->default(0),
                TextInput::make('amount')
                    ->label(__('messages.purchase_medicine.amount') . ':')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->dehydrated(true)
                    ->default(0.00)
                    ->reactive()
                    ->required()
                    ->formatStateUsing(
                        fn($state) =>
                        number_format((float) $state, 2, '.', '')
                    ),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'default' => 1,
                'md' => 2,
                'lg' => 4,
                'xl' => 6,
                '2xl' => 9,
                '3xl' => 9,
            ])
            ->required()
            ->deleteAction(
                fn (Action $action) => $action
                    ->hidden(function ($state, $get) {
                        $items = $get('purchasedMedcines') ?? [];
            
                        return count($items) <= 1;
                    })
            )
            ->afterStateUpdated(function ($state, Set $set, $get) {
                self::updateTotal($get, $set);
            })
            ->dehydrated();
    }
    public static function calculateRowAmount(Set $set, $get): void
    {
        $price = (float) ($get('purchase_price') ?? 0);
        $qty = (int) ($get('quantity') ?? 0);
        $set('amount', ($price * $qty));
    }
    public static function calculateTotals($get): array
    {
        $items = collect($get('purchasedMedcines'))->values()->toArray();

        $totalAmount = 0;
        $taxAmount = 0;
        $grossTotal = 0;

        foreach ($items as $item) {
            $purchasePrice = (float) ($item['purchase_price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $taxPercent = (float) ($item['tax'] ?? 0);

            $rowAmount = $purchasePrice * $quantity;
            $rowTax = $rowAmount * ($taxPercent / 100);

            $totalAmount += $rowAmount;
            $taxAmount += $rowTax;
            $grossTotal += $rowAmount + $rowTax;
        }

        return [
            round($totalAmount, 2),
            round($taxAmount, 2),
            round($grossTotal, 2),
        ];
    }
    public static function updateTotal($get, $set): void
    {
        [$totalAmount, $taxAmount, $grossTotal] = self::calculateTotals($get);
        $discount = (float) ($get('discount') ?? 0);

        $netAmount = max($grossTotal - $discount, 0);

        $netAmount = round($netAmount, 2);

        $set('total', $totalAmount);
        $set('tax', $taxAmount);
        $set('net_amount', $netAmount);
    }

}
