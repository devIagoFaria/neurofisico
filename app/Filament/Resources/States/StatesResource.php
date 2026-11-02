<?php

namespace App\Filament\Resources\States;

use App\Enums\NavigationSort;
use Closure;
use BackedEnum;
use App\Models\State;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\States\Pages\ManageStates;
use Filament\Schemas\Components\Grid;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StatesResource extends Resource
{
    protected static ?string $model = State::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_states');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::GlobeHemisphereEast;
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'States';

    protected static ?int $navigationSort = NavigationSort::STATES->value;
    public static function getModelLabel(): string
    {
        return __('messages.common.state');
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
                            ->required()
                            ->unique(State::class, 'name', ignoreRecord: true)
                            ->rules([
                                function (): Closure {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if (!array_key_exists($value, State::STATE_ARRAY)) {
                                            $fail(__('messages.common.state_not_avl'));
                                        }
                                    };
                                },
                            ]),
                        Select::make('country_id')
                            ->label(__('messages.country.country') . ':')
                            ->placeholder(__('messages.country.country'))
                            ->required()
                            ->relationship('country', 'name')
                            ->preload()
                            ->native(false)
                            ->searchable(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('country.name', 'asc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.web.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('messages.country.country'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_states'))
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.state_update')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.state_delete')),
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
            'index' => ManageStates::route('/'),
        ];
    }
}
