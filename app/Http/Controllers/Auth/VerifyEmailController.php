<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(getDashboardURL() . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(getDashboardURL() . '?verified=1');
    }

    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            Notification::make()
                ->success()
                ->title('Email verified Successfully!')
                ->send();

            return redirect()->route('filament.auth.auth.login');
        }

        if ($user->markEmailAsVerified()) {
            Notification::make()
                ->success()
                ->title('Email verified Successfully!')
                ->send();

            return redirect()->route('filament.auth.auth.login');
        }

        Notification::make()
            ->danger()
            ->title('Failed to verify email!')
            ->send();

        return redirect()->route('filament.auth.auth.login');
    }
}
