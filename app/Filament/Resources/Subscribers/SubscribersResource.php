<?php

namespace App\Filament\Resources\Subscribers;

use BackedEnum;
use App\Models\Subscribe;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Subscribers\Pages\ManageSubscribers;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SubscribersResource extends Resource
{
    protected static ?string $model = Subscribe::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::UserCircleCheck;
    protected static ?int $navigationSort = NavigationSort::SUBSCRIBERS->value;

    public static function canViewAny(): bool
    {
        //return ! isRole('doctor') && ! isRole('patient');
        return false;
    }

    protected static ?string $recordTitleAttribute = 'Subscribers';
    public static function getModelLabel(): string
    {
        return __('messages.subscribers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('email')
                    ->label(__('messages.web.email'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_subscribers'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.subscriber_delete')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubscribers::route('/'),
        ];
    }
}
