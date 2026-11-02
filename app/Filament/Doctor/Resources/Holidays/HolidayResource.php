<?php

namespace App\Filament\Doctor\Resources\Holidays;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use App\Models\DoctorHoliday;
use Filament\Resources\Resource;
use App\Filament\Doctor\Resources\Holidays\Pages\ListHolidays;
use App\Filament\Doctor\Resources\Holidays\Pages\CreateHoliday;
use App\Filament\Doctor\Resources\Holidays\Schemas\HolidayForm;
use App\Filament\Doctor\Resources\Holidays\Tables\HolidaysTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class HolidayResource extends Resource
{
    protected static ?string $model = DoctorHoliday::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarX;
    protected static ?int $navigationSort = NavigationSort::GENERATE_PATIENT_CARDS->value;
    public static function getNavigationLabel(): string
    {
        return (__('messages.holiday.holiday'));
    }

    public static function getModelLabel(): string
    {
        return __('messages.holiday.doctor_holiday');
    }

    protected static ?string $recordTitleAttribute = 'Holiday';

    public static function form(Schema $schema): Schema
    {
        return HolidayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HolidaysTable::configure($table);
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
            'index' => ListHolidays::route('/'),
            'create' => CreateHoliday::route('/create'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
