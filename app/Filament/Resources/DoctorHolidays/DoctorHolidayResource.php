<?php

namespace App\Filament\Resources\DoctorHolidays;

use App\Enums\NavigationSort;
use App\Filament\Clusters\Doctor\DoctorCluster;
use App\Filament\Resources\DoctorHolidays\Pages\CreateDoctorHoliday;
use App\Filament\Resources\DoctorHolidays\Pages\EditDoctorHoliday;
use App\Filament\Resources\DoctorHolidays\Pages\ListDoctorHolidays;
use App\Filament\Resources\DoctorHolidays\Pages\ViewDoctorHoliday;
use App\Filament\Resources\DoctorHolidays\Schemas\DoctorHolidayForm;
use App\Filament\Resources\DoctorHolidays\Schemas\DoctorHolidayInfolist;
use App\Filament\Resources\DoctorHolidays\Tables\DoctorHolidaysTable;
use App\Models\DoctorHoliday;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DoctorHolidayResource extends Resource
{
    protected static ?string $model = DoctorHoliday::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_doctors_holiday');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarX;

    protected static ?int $navigationSort = NavigationSort::DOCTORS_HOLIDAYS->value;

    protected static ?string $cluster = DoctorCluster::class;

    public static function getModelLabel(): string
    {
        return __('messages.holiday.doctor_holiday');
    }

    public static function form(Schema $schema): Schema
    {
        return DoctorHolidayForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorHolidayInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorHolidaysTable::configure($table);
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
            'index' => ListDoctorHolidays::route('/'),
            'create' => CreateDoctorHoliday::route('/create'),
            'view' => ViewDoctorHoliday::route('/{record}'),
            'edit' => EditDoctorHoliday::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
