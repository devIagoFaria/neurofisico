<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class FilamentAuthenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        return route('filament.auth.auth.login');
    }
}
