<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Filament\Actions\Action;

class FilamentResetPassword extends ResetPassword
{
    protected string $view = 'filament.pages.auth.reset_password';

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('auth.email'))
            ->placeholder(__('auth.email'))
            ->disabled()
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('messages.user.new_password'))
            ->placeholder(__('messages.user.new_password'))
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(PasswordRule::default())
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::auth/pages/password-reset/reset-password.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('messages.user.confirm_password'))
            ->placeholder(__('messages.user.confirm_password'))
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    public function getResetPasswordFormAction(): Action
    {
        return Action::make('resetPassword')
            ->label(__('messages.common.reset_password'))
            ->submit('resetPassword')
            ->extraAttributes([
                'class' => 'w-full flex items-center justify-center space-x-3 form-submit'
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getResetPasswordFormAction(),
        ];
    }
}
