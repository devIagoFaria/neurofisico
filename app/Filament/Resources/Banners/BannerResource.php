<?php

namespace App\Filament\Resources\Banners;

use App\Enums\NavigationSort;
use BackedEnum;
use App\Models\Slider;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Clusters\FrontCms\FrontCmsCluster;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Filament\Resources\Banners\Tables\BannersTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class BannerResource extends Resource
{
    protected static ?string $model = Slider::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_front_cms');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Blueprint;
    protected static ?string $cluster = FrontCmsCluster::class;

    protected static ?int $navigationSort = NavigationSort::BANNER->value;

    protected static ?string $recordTitleAttribute = 'Banner';
    public static function getModelLabel(): string
    {
        return __('messages.sliders');
    }
    public static function getNavigationLabel(): string
    {
        return (__('messages.sliders'));
    }

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
