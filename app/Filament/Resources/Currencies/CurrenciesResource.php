<?php

namespace App\Filament\Resources\Currencies;

use App\Enums\NavigationSort;
use App\Models\Currency;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\Currencies\Pages\ManageCurrencies;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CurrenciesResource extends Resource
{
    protected static ?string $model = Currency::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_currencies');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CurrencyDollar;
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'Currencies';

    protected static ?int $navigationSort = NavigationSort::CURRENCIES->value;
    public static function getModelLabel(): string
    {
        return __('messages.currencies');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        TextInput::make('currency_name')
                            ->label(__('messages.common.name') . ':')
                            ->placeholder(__('messages.currency.currency_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('currency_icon')
                            ->label(__('messages.currency.currency_icon') . ':')
                            ->placeholder(__('messages.currency.currency_icon'))
                            ->required(),
                        TextInput::make('currency_code')
                            ->label(__('messages.currency.currency_code') . ':')
                            ->placeholder(__('messages.currency.currency_code'))
                            ->required()
                            ->belowContent(new HtmlString(
                                '<strong>' . __('messages.visit.notes') . ':</strong> ' .
                                    __('messages.currency.add_currency_code_as_per_three_letter_iso_code') . '.' .
                                    ' <a href="https://stripe.com/docs/currencies" target="_blank" class="text-primary-600 underline">
                                    ' . __('messages.currency.you_can_find_out_here') . '.' . '
                                </a>'
                            )),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordTitleAttribute('Currencies')
            ->columns([
                TextColumn::make('currency_name')
                    ->label(__('messages.currency.currency_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency_icon')
                    ->label(__('messages.currency.currency_icon'))
                    ->searchable(),
                TextColumn::make('currency_code')
                    ->label(__('messages.currency.currency_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.web.created_at'))
                    ->sortable()
                    ->dateTime('Y-m-d H:i:s'),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('id', 'desc')
            ->emptyStateHeading(__('messages.custom_table_message.no_currencies'))
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.currency_update')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.currency_delete')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencies::route('/'),
        ];
    }
}
