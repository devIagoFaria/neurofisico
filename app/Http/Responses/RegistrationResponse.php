<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse as BaseRegistrationResponse;

class RegistrationResponse implements BaseRegistrationResponse
{
    public function toResponse($request)
    {
        return redirect()->route('filament.auth.auth.login');
    }
}
