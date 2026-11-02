<?php

namespace App\Http\Responses;

use App\Models\Role;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as ContractsLoginResponse;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements ContractsLoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = getLogInUser();

        if (getSettingValue('email_verified') &&  !$user->email_verified_at) {
            Auth::logout();
            Notification::make()
                ->title('Account Inactive')
                ->body(__('messages.setting.do_not_allow_to_login_without_email_verification'))
                ->danger()
                ->persistent()
                ->send();
            return redirect()->route('filament.auth.auth.login');
        }

        if ($user) {
            $loginURL = getDashboardURL() ?? null;
            if ($loginURL) {
                return redirect()->to($loginURL);
            }
        }

        return redirect()->to(
            Filament::getCurrentPanel()->getUrl()
        );
    }
}
