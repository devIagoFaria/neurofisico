<?php

namespace App\Filament\Resources\UsedMedicines\Tables;

use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class UsedMedicinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('medicine.name')
                    ->label(__('messages.medicine.medicines'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale_quantity')
                    ->label(__('messages.used_medicine.used_quantity'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('medicineBill.model_type')
                    ->label(__('messages.used_medicine.used_at'))
                    ->formatStateUsing(function ($record) {
                        $modelType = $record->medicineBill->model_type ?? null;
                
                        return match (class_basename($modelType)) {
                            'Prescription' => __('messages.prescription.prescription'),
                            'MedicineBill' => __('messages.medicine_bills.medicine_bill'),
                            default => __('messages.common.n/a'),
                        };
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.appointment.date'))
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => Carbon::parse($state)->format('g:i A').'<br>'.Carbon::parse($state)->format('jS M, Y')
                    )
                    ->html(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine_used'))
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
