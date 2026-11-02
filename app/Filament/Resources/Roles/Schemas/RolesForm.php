<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RolesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('display_name')
                                    ->label(__('messages.common.name') . ':')
                                    ->required()
                                    ->unique()
                                    ->unique('roles', 'display_name'),
                                CheckboxList::make('permissions')
                                    ->label(__('messages.role.role_permissions') . ':')
                                    ->options(function () {
                                        return Permission::toBase()->where('name', '!=', 'manage_admin_dashboard')->get()->pluck('display_name', 'id')->toArray();
                                    })
                                    ->bulkToggleable()
                                    ->relationship(
                                        'permissions',
                                        'display_name',
                                        modifyQueryUsing: fn($query) => $query->where('name', '!=', 'manage_admin_dashboard')
                                    )
                                    ->columns(2)
                                    ->columnSpanFull()
                            ])
                    ])->columnSpanFull(),
            ]);
    }
}
