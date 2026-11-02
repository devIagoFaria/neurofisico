<?php

namespace App\Filament\Resources\DoctorSchedules;

use App\Enums\NavigationSort;
use App\Filament\Clusters\Doctor\DoctorCluster;
use App\Filament\Resources\DoctorSchedules\Pages\CreateDoctorSchedule;
use App\Filament\Resources\DoctorSchedules\Pages\EditDoctorSchedule;
use App\Filament\Resources\DoctorSchedules\Pages\ListDoctorSchedules;
use App\Filament\Resources\DoctorSchedules\Pages\ViewDoctorSchedule;
use App\Filament\Resources\DoctorSchedules\Schemas\DoctorScheduleForm;
use App\Filament\Resources\DoctorSchedules\Schemas\DoctorScheduleInfolist;
use App\Filament\Resources\DoctorSchedules\Tables\DoctorSchedulesTable;
use App\Models\DoctorSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DoctorScheduleResource extends Resource
{
    protected static ?string $model = DoctorSession::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_doctor_sessions');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarDots;

    protected static ?string $cluster = DoctorCluster::class;

    protected static ?int $navigationSort = NavigationSort::DOCTORS_SCHEDULES->value;

    protected static ?string $recordTitleAttribute = 'doctor';

    public static function getModelLabel(): string
    {
        return __('messages.doctor_session.doctor_session');
    }

    public static function form(Schema $schema): Schema
    {
        return DoctorScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorSchedulesTable::configure($table);
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
            'index' => ListDoctorSchedules::route('/'),
            'create' => CreateDoctorSchedule::route('/create'),
            'view' => ViewDoctorSchedule::route('/{record}'),
            'edit' => EditDoctorSchedule::route('/{record}/edit'),
        ];
    }
}
