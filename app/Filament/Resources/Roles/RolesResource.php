<?php

namespace App\Filament\Resources\Roles;

use App\Enums\NavigationSort;
use BackedEnum;
use App\Models\Role;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Resources\Roles\Pages\EditRoles;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\Roles\Pages\CreateRoles;
use App\Filament\Resources\Roles\Schemas\RolesForm;
use App\Filament\Resources\Roles\Tables\RolesTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class RolesResource extends Resource
{
    protected static ?string $model = Role::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_roles');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::UserCircleDashed;
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'Roles';

    protected static ?int $navigationSort = NavigationSort::ROLES->value;
    public static function getModelLabel(): string
    {
        return __('messages.roles');
    }

    public static function form(Schema $schema): Schema
    {
        return RolesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRoles::route('/create'),
            'edit' => EditRoles::route('/{record}/edit'),
        ];
    }
}
