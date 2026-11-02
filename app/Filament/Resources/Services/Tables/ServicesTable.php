<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Contracts\Database\Query\Builder;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                SpatieMediaLibraryImageColumn::make('icon')
                    ->label(__('messages.front_service.icon'))
                    ->circular()
                    ->collection('icon')
                    ->defaultImageUrl(asset('images/hospital-services.png')),
                TextColumn::make('name')
                    ->label(__('messages.web.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category_id')
                    ->label(__('messages.medicine.category'))
                    ->formatStateUsing(function ($record) {
                        return $record->serviceCategory->name;
                    })
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('serviceCategory', fn ($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                TextColumn::make('charges')
                    ->label(__('messages.service.charges'))
                    ->formatStateUsing(fn ($record) => getCurrencyIcon($record->currency_id) . number_format($record->charges, 2))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label(__('messages.web.status'))
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        notify(__('messages.flash.status_update'));
                    }),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_services'))
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->action(function ($record) {
                        $used = Appointment::whereServiceId($record->id)->exists();
                        if ($used) {
                            return Notification::make()
                                ->title(__('messages.flash.service_use'))
                                ->danger()
                                ->send();
                        }
                        $record->delete();
                        return Notification::make()
                            ->title(__('messages.flash.service_delete'))
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle(null),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
