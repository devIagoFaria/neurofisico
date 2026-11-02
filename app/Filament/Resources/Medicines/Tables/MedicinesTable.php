<?php

namespace App\Filament\Resources\Medicines\Tables;

use App\Models\PurchasedMedicine;
use App\Models\SaleMedicine;
use App\Models\UsedMedicine;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Contracts\View\View;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;

class MedicinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.medicine.medicine'))
                    ->sortable()
                    ->searchable()
                    ->action(ViewAction::make())
                    ->color('primary')
                    ->extraAttributes([
                        'class' => 'cursor-pointer'
                    ])
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('brand.name')
                    ->label(__('messages.medicine.brand'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('available_quantity')
                    ->label(__('messages.medicine.available_quantity'))
                    ->badge()
                    ->state(function ($record) {
                        $totalPurchased = PurchasedMedicine::where('medicine_id', $record->id)->sum('quantity');
                
                        $totalSold = SaleMedicine::where('medicine_id', $record->id)->sum('sale_quantity');
                
                        return max($totalPurchased - $totalSold, 0);
                    }),
                TextColumn::make('selling_price')
                    ->label(__('messages.medicine.selling_price'))
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . number_format($record->selling_price, 2))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('buying_price')
                    ->label(__('messages.medicine.buying_price'))
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . number_format($record->buying_price, 2))
                    ->sortable()
                    ->searchable(),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine'))
            ->recordActions([
                ViewAction::make()->iconButton()->extraAttributes(['class' => 'hidden']),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(null)
                    ->action(function ($record) {
                        $purchaseMedicine = PurchasedMedicine::whereMedicineId($record->id)->first();
                        $saleMedicine = SaleMedicine::whereMedicineId($record->id)->first();

                        if (isset($purchaseMedicine) && ! empty($purchaseMedicine)) {
                            Notification::make()
                                ->title(__('messages.medicine.medicine') . ' ' . __('messages.medicine.cant_be_deleted'))
                                ->danger()
                                ->send();
                            return;
                        }

                        if (isset($saleMedicine) && ! empty($saleMedicine)) {
                            Notification::make()
                                ->title(__('messages.medicine.medicine') . ' ' . __('messages.medicine.cant_be_deleted'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->delete();
                        Notification::make()
                            ->title(__('messages.flash.medicine_delete'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
