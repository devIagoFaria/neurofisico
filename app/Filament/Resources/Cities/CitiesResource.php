<?php

namespace App\Filament\Resources\Cities;

use App\Enums\NavigationSort;
use Closure;
use BackedEnum;
use App\Models\City;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\Cities\Pages\ManageCities;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CitiesResource extends Resource
{
    protected static ?string $model = City::class;
    
    public static function canViewAny(): bool
    {
        return checkPermission('manage_cities');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::City;
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'Cities';

    protected static ?int $navigationSort = NavigationSort::CITIES->value;
    public static function getModelLabel(): string
    {
        return __('messages.common.city');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.medicine.name') . ':')
                            ->required()
                            ->placeholder(__('messages.medicine.name'))
                            ->unique(City::class, 'name', ignoreRecord: true)
                            ->rules([
                            function (): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (! in_array($value, City::CITY_ARRAY, true)) {
                                        $fail(__('messages.city.city_not_avl'));
                                    }
                                };
                            },
                        ]),
                        Select::make('state_id')
                            ->required()
                            ->label(__('messages.state.state') . ':')
                            ->placeholder(__('messages.state.state'))
                            ->relationship('state', 'name')
                            ->preload()
                            ->native(false)
                            ->searchable(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('state.name', 'asc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.web.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('state.name')
                    ->label(__('messages.state.state'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_cities'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.city_update')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.city_delete')),
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
            'index' => ManageCities::route('/'),
        ];
    }
}
