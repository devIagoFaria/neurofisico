<?php

namespace App\Filament\Resources\SmartPatientCards\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Tables\Columns\ColorPickerColumn;
use App\Filament\Tables\Columns\EditableColorColumn;

class SmartPatientCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('template_name')
                    ->label(__('messages.smart_patient_card.templat_name'))
                    ->sortable()
                    ->searchable(),
                ColorPickerColumn::make('header_color')
                    ->label(__('messages.smart_patient_card.header_color'))
                    ->sortable()
                    ->searchable()
                    ->updateStateUsing(function ($record, $state) {
                        $record->header_color = $state;
                        $record->save();
                        notify(__('messages.smart_patient_card.template_update'));
                    }),
                ToggleColumn::make('show_email')
                    ->label(__('messages.smart_patient_card.email_show'))
                    ->sortable()
                    ->afterStateUpdated(fn() => notify(__('messages.smart_patient_card.template_update'))),
                ToggleColumn::make('show_phone')
                    ->label(__('messages.smart_patient_card.phone_show'))
                    ->sortable()
                    ->afterStateUpdated(fn() => notify(__('messages.smart_patient_card.template_update'))),
                ToggleColumn::make('show_dob')
                    ->label(__('messages.smart_patient_card.dob_show'))
                    ->sortable()
                    ->afterStateUpdated(fn() => notify(__('messages.smart_patient_card.template_update'))),
                ToggleColumn::make('show_blood_group')
                    ->label(__('messages.smart_patient_card.blood_group_show'))
                    ->sortable()
                    ->afterStateUpdated(fn() => notify(__('messages.smart_patient_card.template_update'))),
                ToggleColumn::make('show_address')
                    ->label(__('messages.smart_patient_card.address_show'))
                    ->sortable()
                    ->afterStateUpdated(fn() => notify(__('messages.smart_patient_card.template_update'))),
                ToggleColumn::make('show_patient_unique_id')
                    ->label(__('messages.smart_patient_card.unique_id_show'))
                    ->sortable()
                    ->afterStateUpdated(fn() => notify(__('messages.smart_patient_card.template_update'))),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_smart_card_template'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(__('messages.smart_patient_card.template_deleted')),
            ])
            ->bulkActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
