<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RolesResource;
use App\Models\Role;
use Filament\Resources\Pages\EditRecord;

class EditRoles extends EditRecord
{
    protected static string $resource = RolesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Role
    {
        $displayName = strtolower($data['display_name']);
        $data['name'] = str_replace(' ', '_', $displayName);

        $record->update($data);

        if (isset($data['permissions'])) {
            $record->permissions()->sync($data['permissions']);
        }

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.role_update');
    }
}
