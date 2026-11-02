<?php

namespace App\Filament\Resources\PurchaseMedicines\Pages;

use App\Models\PurchaseMedicine;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\IconPosition;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Contracts\Database\Query\Builder;
use App\Filament\Resources\PurchaseMedicines\PurchaseMedicinesResource;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class ListPurchaseMedicines extends ListRecords
{
    protected static string $resource = PurchaseMedicinesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                CreateAction::make()
                    ->icon('heroicon-m-plus')
                    ->label(__('messages.purchase_medicine.purchase_medicine')),

                ExportAction::make()
                    ->label(__('messages.purchase_medicine.export_to_excel'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->disabled(fn () => PurchaseMedicine::count() === 0)
                    ->exports([
                        ExcelExport::make()
                            ->withFilename(
                                __('messages.purchase_medicine.purchase_medicine') . '-' . now()->format('Y-m-d') . '.xlsx'
                            )

                            ->withColumns([
                                // Row Number
                                Column::make('id')
                                    ->heading(__('messages.no'))
                                    ->formatStateUsing(function () {
                                        static $counter = 1;
                                        return $counter++;
                                    }),
                                // Purchase Number
                                Column::make('purchase_no')
                                    ->heading(__('messages.purchase_medicine.purchase_number'))
                                    ->formatStateUsing(fn($state) => '#' . $state),

                                // Total
                                Column::make('total')
                                    ->heading(__('messages.purchase_medicine.total')),

                                // Tax
                                Column::make('tax')
                                    ->heading(__('messages.purchase_medicine.tax')),

                                // Discount
                                Column::make('discount')
                                    ->heading(__('messages.purchase_medicine.discount')),

                                // Net Amount
                                Column::make('net_amount')
                                    ->heading(__('messages.purchase_medicine.net_amount')),

                                // Payment Mode
                                Column::make('payment_type')
                                    ->heading(__('messages.purchase_medicine.payment_mode'))
                                    ->formatStateUsing(
                                        fn($state) =>
                                        PurchaseMedicine::PAYMENT_METHOD[$state]
                                            ?? __('messages.common.n/a')
                                    ),
                            ]),
                    ]),
            ])
                ->label(__('messages.purchase_medicine.actions'))
                ->icon('heroicon-m-chevron-down')
                ->iconPosition(IconPosition::After)
                ->button(),
        ];
    }
}
