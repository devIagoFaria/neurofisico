<?php

namespace App\Filament\Resources\Doctors;

use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Clusters\Doctor\DoctorCluster;
use App\Filament\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Resources\Doctors\Pages\ViewDoctor;
use App\Filament\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Resources\Doctors\Schemas\DoctorForm;
use App\Filament\Resources\Doctors\Tables\DoctorsTable;
use App\Filament\Resources\Doctors\Schemas\DoctorInfolist;

class DoctorResource extends Resource
{
    protected static ?string $model = User::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_doctors');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Stethoscope;

    protected static ?int $navigationSort = NavigationSort::DOCTORS->value;

    protected static ?string $cluster = DoctorCluster::class;

    public static function getModelLabel(): string
    {
        return __('messages.doctor.doctor');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Médicos');
    }

    public static function form(Schema $schema): Schema
    {
        return DoctorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctors::route('/'),
            'create' => CreateDoctor::route('/create'),
            'view' => ViewDoctor::route('/{record}'),
            'edit' => EditDoctor::route('/{record}/edit'),
        ];
    }
}
