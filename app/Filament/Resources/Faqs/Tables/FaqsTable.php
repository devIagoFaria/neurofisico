<?php

namespace App\Filament\Resources\Faqs\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('question')
                    ->label(__('messages.faq.question'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(3),
                TextColumn::make('answer')
                    ->label(__('messages.faq.answer'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(4),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_faqs'))
            ->filters([
                //
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.faq_delete')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
