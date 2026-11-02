<?php

namespace App\Filament\Resources\Countries;

use App\Enums\NavigationSort;
use App\Models\Country;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\Countries\Pages\ManageCountries;
use Dom\Text;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CountriesResource extends Resource
{
    protected static ?string $model = Country::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_countries');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Globe;
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'Countries';

    protected static ?int $navigationSort = NavigationSort::COUNTRIES->value;
    public static function getModelLabel(): string
    {
        return __('messages.common.country');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.medicine.name') . ':')
                            ->placeholder(__('messages.medicine.name'))
                            ->unique(Country::class, 'name', ignoreRecord: true)
                            ->required(),
                        TextInput::make('short_code')
                            ->label(__('messages.country.short_code') . ':')
                            ->placeholder(__('messages.country.short_code'))
                            ->unique(Country::class, 'short_code', ignoreRecord: true)
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.web.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('short_code')
                    ->label(__('messages.country.short_code'))
                    ->sortable()
                    ->searchable(),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_countries'))
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.country_update')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.country_delete')),
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
            'index' => ManageCountries::route('/'),
        ];
    }
}
