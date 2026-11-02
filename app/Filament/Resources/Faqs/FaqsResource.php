<?php

namespace App\Filament\Resources\Faqs;

use App\Enums\NavigationSort;
use App\Models\Faq;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Resources\Faqs\Pages\EditFaqs;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\Faqs\Pages\CreateFaqs;
use App\Filament\Resources\Faqs\Schemas\FaqsForm;
use App\Filament\Resources\Faqs\Tables\FaqsTable;
use App\Filament\Clusters\FrontCms\FrontCmsCluster;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class FaqsResource extends Resource
{
    protected static ?string $model = Faq::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_front_cms');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::SealQuestion;
    protected static ?string $cluster = FrontCmsCluster::class;

    protected static ?int $navigationSort = NavigationSort::FAQS->value;

    protected static ?string $recordTitleAttribute = 'Faqs';
    public static function getModelLabel(): string
    {
        return __('messages.faqs');
    }
    public static function getNavigationLabel(): string
    {
        return (__('messages.faqs'));
    }

    public static function form(Schema $schema): Schema
    {
        return FaqsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqsTable::configure($table);
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
            'index' => ListFaqs::route('/'),
            'create' => CreateFaqs::route('/create'),
            'edit' => EditFaqs::route('/{record}/edit'),
        ];
    }
}
