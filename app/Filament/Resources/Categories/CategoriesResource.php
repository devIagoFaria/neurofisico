<?php

namespace App\Filament\Resources\Categories;

use App\Enums\NavigationSort;
use App\Filament\Clusters\Medicines\MedicinesCluster;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CategoriesResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_medicines');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Bandaids;

    protected static ?string $cluster = MedicinesCluster::class;

    protected static ?string $recordTitleAttribute = 'Medicine Categories';

    protected static ?int $navigationSort = NavigationSort::MEDICINE_CATEGORIES->value;

    public static function getModelLabel(): string
    {
        return __('messages.medicine.medicine_category');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('messages.admin_dashboard.name').':')
                    ->placeholder(__('messages.admin_dashboard.name'))
                    ->validationAttribute(__('messages.admin_dashboard.name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(Category::class, 'name', ignoreRecord: true)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label(__('messages.common.active').':')
                    ->inline(false)
                    ->required()
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->recordTitleAttribute('Medicine Categories')
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.admin_dashboard.name'))
                    ->sortable()
                    ->searchable()
                    ->html()
                    ->formatStateUsing(fn ($state, $record) => '<a href="'.CategoriesResource::getUrl('view', ['record' => $record->id]).'"class="hoverLink">'.$state.'</a>')
                    ->color('primary')
                    ->weight(FontWeight::SemiBold),
                ToggleColumn::make('is_active')
                    ->label(__('messages.common.active'))
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        notify(__('messages.flash.status_update'));
                    }),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->options([
                        Category::Deactive => __('messages.common.de_active'),
                        Category::ACTIVE => __('messages.common.active'),
                    ])
                    ->native(false)
                    ->label(__('messages.web.status')),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine_category'))
            ->deferFilters(false)
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth(Width::Medium)
                    ->successNotificationTitle(__('messages.flash.medicine_category_update')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(null)
                    ->action(function ($record) {
                        if ($record->medicines->count() > 0) {
                            Notification::make()
                                ->title(__('messages.medicine.medicine_category').' '.__('messages.medicine.cant_be_deleted'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title(__('messages.flash.medicine_category_delete'))
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

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
            'view' => ViewCategory::route('/{record}'),
        ];
    }
}
