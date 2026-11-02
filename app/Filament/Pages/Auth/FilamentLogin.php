<?php

namespace App\Filament\Pages\Auth;

use App\Http\Responses\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Schemas\Schema;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
class FilamentLogin extends Login
{
    protected string $view = 'filament.pages.auth.login';

    public ?string $captcha_token = null;
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent()
                    ->label(__('auth.email'))
                    ->placeholder(__('auth.login.enter_email')),
                $this->getPasswordFormComponent()
                    ->label(__('auth.password'))
                    ->placeholder(__('auth.login.enter_password')),
                ViewField::make('captcha_token')
                    ->view('filament.forms.components.recaptcha')
                    ->visible(fn () => isCaptchaEnabled()),
                $this->getRememberFormComponent()
                    ->label(__('auth.remember_me')),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }
        $data = $this->form->getState();

        if (isCaptchaEnabled()) {
            validator(
                ['captcha_token' => $data['captcha_token'] ?? null],
                ['captcha_token' => ['required', 'captcha']],
                [
                    'captcha_token.required' => __('auth.flash.captcha_required'),
                    'captcha_token.captcha' => __('auth.flash.captcha_failed'),
                ]
            )->validate();
        }

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        $authProvider = $authGuard->getProvider();
        /** @phpstan-ignore-line */
        $credentials = $this->getCredentialsFromFormData($data);

        $user = $authProvider->retrieveByCredentials($credentials);

        if ((! $user) || (! $authProvider->validateCredentials($user, $credentials))) {
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if (
            filled($this->userUndertakingMultiFactorAuthentication) &&
            (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
        ) {
            $this->multiFactorChallengeForm->validate();
        } else {
            foreach (Filament::getMultiFactorAuthenticationProviders() as $multiFactorAuthenticationProvider) {
                if (! $multiFactorAuthenticationProvider->isEnabled($user)) {
                    continue;
                }

                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());

                if ($multiFactorAuthenticationProvider instanceof HasBeforeChallengeHook) {
                    $multiFactorAuthenticationProvider->beforeChallenge($user);
                }

                break;
            }

            if (filled($this->userUndertakingMultiFactorAuthentication)) {
                $this->multiFactorChallengeForm->fill();

                return null;
            }
        }

        if ($user instanceof \App\Models\User) {

            if ($user->email_verified_at === null && getSettingValue('email_verified')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.email' => __('auth.flash.email_verify'),
                ]);
            }
        
            if (! $user->status) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.email' => __('auth.flash.status_inactive'),
                ]);
            }
        }

        if (! $authGuard->attemptWhen($credentials, function (Authenticatable $user): bool {
            if (! ($user instanceof FilamentUser)) {
                return true;
            }

            return $user->canAccessPanel(Filament::getCurrentOrDefaultPanel());
        }, $data['remember'] ?? false)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
