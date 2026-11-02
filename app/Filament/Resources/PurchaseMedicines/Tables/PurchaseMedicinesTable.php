<?php

namespace App\Filament\Resources\PurchaseMedicines\Tables;

use App\Models\PurchaseMedicine;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;

class PurchaseMedicinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('purchase_no')
                    ->label(__('messages.purchase_medicine.purchase_number'))
                    ->badge(),
                TextColumn::make('total')
                    ->label(__('messages.purchase_medicine.total'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                TextColumn::make('tax')
                    ->label(__('messages.purchase_medicine.tax'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                TextColumn::make('discount')
                    ->label(__('messages.purchase_medicine.discount'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                TextColumn::make('net_amount')
                    ->label(__('messages.purchase_medicine.net_amount'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                TextColumn::make('payment_type')
                    ->label(__('messages.purchase_medicine.payment_mode'))
                    ->getStateUsing(fn ($record) => match ($record->payment_type) {
                        PurchaseMedicine::CASH => __('messages.payment_method.cash'),
                        PurchaseMedicine::CHEQUE => __('messages.payment_method.cheque'),
                        PurchaseMedicine::OTHER => __('messages.payment_method.other'),
                        default => __('messages.common.n/a'),
                    }),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                SelectFilter::make('payment_type')
                    ->label(__('messages.purchase_medicine.payment_mode'))
                    ->options([
                        PurchaseMedicine::CASH => __('messages.payment_method.cash'),
                        PurchaseMedicine::CHEQUE => __('messages.payment_method.cheque'),
                        PurchaseMedicine::OTHER => __('messages.payment_method.other'),
                    ])
                    ->native(false)
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine_purchase'))
            ->recordActions([
                // EditAction::make()->iconButton(),
                ViewAction::make()
                    ->iconButton()
                    ->color('primary'),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(__('messages.flash.medicine_delete')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
