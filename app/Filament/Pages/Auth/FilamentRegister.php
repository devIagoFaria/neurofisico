<?php

namespace App\Filament\Pages\Auth;

use App\Http\Responses\RegistrationResponse;
use App\Models\Patient;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Events\Registered;
use Filament\Auth\Pages\Register;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;

class FilamentRegister extends Register
{
    protected string $view = 'filament.pages.auth.register';

    public ?string $captcha_token = null;
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label(__('messages.patient.first_name'))
                    ->placeholder(__('messages.patient.first_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('messages.patient.last_name'))
                    ->placeholder(__('messages.patient.last_name'))
                    ->required()
                    ->maxLength(255),
                $this->getEmailFormComponent()
                    ->label(__('messages.patient.email'))
                    ->placeholder(__('messages.patient.email'))
                    ->columnSpanFull(),
                $this->getPasswordFormComponent()
                    ->label(__('messages.patient.password'))
                    ->placeholder(__('messages.patient.password'))
                    ->extraAttributes(['class' => 'password-field']),
                $this->getPasswordConfirmationFormComponent()
                    ->label(__('messages.patient.confirm_password'))
                    ->placeholder(__('messages.patient.confirm_password'))
                    ->extraAttributes(['class' => 'password-field']),
                ViewField::make('captcha_token')
                    ->view('filament.forms.components.recaptcha')
                    ->visible(fn () => isCaptchaEnabled())
                    ->columnSpanFull(),
                Checkbox::make('remember')
                    ->required()
                    ->label(new HtmlString(
                        '<span class="ml-1">'
                        . __('auth.registration.i_agree') . ' '
                        . '<a href="' . route('terms.conditions') . '" class="ml-1 underline">'
                        . __('auth.registration.terms_and_condition')
                        . '</a>.'
                        . '</span>'
                    ))
                    ->columnSpanFull(),
            ])->columns(2);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction()
                ->extraAttributes(['class' => 'w-full flex items-center justify-center space-x-3 form-submit'])
                ->label(__('messages.common.submit')),
        ];
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
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

        $user = $this->wrapInDatabaseTransaction(function (): Model {
            $data = $this->form->getState();

            $data = $this->mutateFormDataBeforeRegister($data);

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $user->patient()->create([
                'patient_unique_id' => mb_strtoupper(Patient::generatePatientUniqueId()),
            ]);

            $user->assignRole(User::PATIENT);

            return $user;
        });

        event(new Registered($user));

        $user->sendEmailVerificationNotification();

        Notification::make()
        ->success()
        ->title(__('auth.flash.verify_email_message'))
        ->send();

        // Filament::auth()->login($user);

        // session()->regenerate();

        return app(RegistrationResponse::class);
    }
}
