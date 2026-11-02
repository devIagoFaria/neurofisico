<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label(__('messages.common.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('permissions.display_name')
                    ->label(__('messages.role.permissions'))
                    ->badge()
                    ->wrap(true)
                    ->color(fn($state) => getBadgeColor(crc32($state)))
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('id', 'desc')
            ->emptyStateHeading(__('messages.custom_table_message.no_roles'))
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.role_update'))
                    ->hidden(fn ($record) => in_array($record->name, ['clinic_admin'])),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.role_delete'))
                    ->hidden(fn ($record) => in_array($record->name, ['clinic_admin', 'doctor', 'patient'])),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'));
    }
}
