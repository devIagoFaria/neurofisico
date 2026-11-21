<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MedicalRecord $record): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $record->user_id !== null
            && (int) $record->user_id === (int) $user->id;
    }

    public function delete(User $user, MedicalRecord $record): bool
    {
        return $record->user_id !== null
            && (int) $record->user_id === (int) $user->id;
    }
}