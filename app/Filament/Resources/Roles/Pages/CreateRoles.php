<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RolesResource;
use App\Models\Role;
use Filament\Resources\Pages\CreateRecord;

class CreateRoles extends CreateRecord
{
    protected static string $resource = RolesResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function handleRecordCreation(array $data): Role
    {
        $displayName = strtolower($data['display_name']);
        $data['name'] = str_replace(' ', '_', $displayName);

        $role = Role::create($data);

        if (isset($data['permissions']) && ! empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return $role;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.role_create');
    }
}
