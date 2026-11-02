<?php

namespace App\Filament\Resources\Enquiries;

use BackedEnum;
use App\Models\Enquiry;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Enquiries\Pages\ViewEnquiry;
use App\Filament\Resources\Enquiries\Pages\ManageEnquiries;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class EnquiriesResource extends Resource
{
    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Question;
    protected static ?int $navigationSort = NavigationSort::ENQUIRIES->value;

    public static function canViewAny(): bool
    {
        //return ! isRole('doctor') && ! isRole('patient');
        return false;
    }

    protected static ?string $recordTitleAttribute = 'Enquiries';

    public static function getModelLabel(): string
    {
        return __('messages.web.enquiry');
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
                TextColumn::make('name')
                    ->label(__('messages.web.name'))
                    ->searchable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('message')
                    ->label(__('messages.web.message'))
                    ->sortable()
                    ->limit(55)
                    ->searchable(),
                TextColumn::make('view')
                    ->label(__('messages.web.status'))
                    ->sortable()
                    ->badge()
                    ->state(fn ($record) => $record->view
                        ? __('messages.common.read')
                        : __('messages.common.unread')
                    )
                    ->color(fn ($record) => $record->view ? 'success' : 'danger'),
                TextColumn::make('created_at')
                    ->label(__('messages.web.created_at'))
                    ->sortable()
                    ->badge()
                    ->dateTime('d M Y g:i A'),
            ])
            ->deferFilters(false)
            ->filters([
                SelectFilter::make('view')
                    ->label(__('messages.web.status'))
                    ->options([
                        '0' => __('messages.common.unread'),
                        '1' => __('messages.common.read'),
                    ])
                    ->native(false),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_enquiries'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->color('primary'),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.enquire_deleted')),
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
            'index' => ManageEnquiries::route('/'),
            'view' => ViewEnquiry::route('/{record}'),
        ];
    }
}
