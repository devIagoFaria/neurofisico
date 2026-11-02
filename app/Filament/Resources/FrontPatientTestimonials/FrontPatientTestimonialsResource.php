<?php

namespace App\Filament\Resources\FrontPatientTestimonials;

use App\Enums\NavigationSort;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\FrontPatientTestimonial;
use App\Filament\Clusters\FrontCms\FrontCmsCluster;
use App\Filament\Resources\FrontPatientTestimonials\Pages\EditFrontPatientTestimonials;
use App\Filament\Resources\FrontPatientTestimonials\Pages\ListFrontPatientTestimonials;
use App\Filament\Resources\FrontPatientTestimonials\Pages\CreateFrontPatientTestimonials;
use App\Filament\Resources\FrontPatientTestimonials\Schemas\FrontPatientTestimonialsForm;
use App\Filament\Resources\FrontPatientTestimonials\Tables\FrontPatientTestimonialsTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class FrontPatientTestimonialsResource extends Resource
{
    protected static ?string $model = FrontPatientTestimonial::class;

    public static function canViewAny(): bool
    {
        //return checkPermission('manage_front_cms');
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::ChatCircleText;
    protected static ?string $cluster = FrontCmsCluster::class;

    protected static ?int $navigationSort = NavigationSort::FRONT_PATIENT_TESTIMONIALS->value;

    protected static ?string $recordTitleAttribute = 'Front Patient Testimonials';
    public static function getModelLabel(): string
    {
        return __('messages.front_patient_testimonial.front_patient_testimonial');
    }

    public static function form(Schema $schema): Schema
    {
        return FrontPatientTestimonialsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FrontPatientTestimonialsTable::configure($table);
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
            'index' => ListFrontPatientTestimonials::route('/'),
            'create' => CreateFrontPatientTestimonials::route('/create'),
            'edit' => EditFrontPatientTestimonials::route('/{record}/edit'),
        ];
    }
}
