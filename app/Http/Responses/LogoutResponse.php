<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as ContractsLogoutResponse;
use Filament\Notifications\Notification;

class LogoutResponse implements ContractsLogoutResponse
{
    public function toResponse($request)
    {
        return redirect()->route('filament.auth.auth.login');
    }
}
