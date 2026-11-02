<?php

namespace App\Filament\Resources\ServiceCategories;

use App\Enums\NavigationSort;
use BackedEnum;
use App\Models\Service;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\ServiceCategory;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Count;
use Illuminate\Contracts\Database\Query\Builder;
use App\Filament\Clusters\Services\ServicesCluster;
use App\Filament\Resources\ServiceCategories\Pages\ManageServiceCategories;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ServiceCategoriesResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_services');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Tag;

    protected static ?string $cluster = ServicesCluster::class;

    protected static ?int $navigationSort = NavigationSort::SERVICE_CATEGORIES->value;

    protected static ?string $recordTitleAttribute = 'Service Categories';

    public static function getModelLabel(): string
    {
        return __('messages.service_category.service_category');
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('messages.web.name') . ':')
                    ->placeholder(__('messages.web.name'))
                    ->required()
                    ->unique(ServiceCategory::class, 'name', ignoreRecord: true)
                    ->columnSpanFull(),
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
                    ->sortable(),
                TextColumn::make('services_count')
                    ->label(__('messages.web.total_services'))
                    ->badge()
                    ->color('danger')
                    ->counts('services')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_services_category'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth(Width::Medium)
                    ->successNotificationTitle(__('messages.flash.service_cat_update')),
                DeleteAction::make()
                    ->iconButton()
                    ->action(function ($record) {
                        $used = Service::whereCategoryId($record->id)->exists();
                        if ($used) {
                            return Notification::make()
                                ->title(__('messages.flash.service_cat_use'))
                                ->danger()
                                ->send();
                        }
                        $record->delete();
                        return Notification::make()
                            ->title(__('messages.flash.service_cat_delete'))
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

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceCategories::route('/'),
        ];
    }
}
