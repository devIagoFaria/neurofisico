<?php

namespace App\Filament\Resources\MedicineBrands\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\MedicineBrands\MedicineBrandResource;
use Filament\Notifications\Notification;

class MedicineBrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.medicine.brand'))
                    ->sortable()
                    ->searchable()
                    ->html()
                    ->formatStateUsing(fn($state, $record) => '<a href="' . MedicineBrandResource::getUrl('view', ['record' => $record->id]) . '"class="hoverLink">' . $state . '</a>')
                    ->color('primary')
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('email')
                    ->label(__('messages.web.email'))
                    ->sortable()
                    ->searchable()
                    ->default(__('messages.common.n/a')),
                TextColumn::make('phone')
                    ->label(__('messages.web.phone'))
                    ->sortable()
                    ->searchable()
                    ->default(__('messages.common.n/a')),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine_brand'))
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(null)
                    ->action(function ($record) {
                        if ($record->medicines->count() > 0) {
                            Notification::make()
                                ->title(__('messages.medicine.medicine_brand') . ' ' . __('messages.medicine.cant_be_deleted'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title(__('messages.flash.medicine_brand_delete'))
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
