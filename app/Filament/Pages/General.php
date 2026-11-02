<?php

namespace App\Filament\Pages;

use App\Enums\NavigationSort;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Forms\Components\PhoneInput;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Specialization;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use libphonenumber\PhoneNumberUtil;
use Tapp\FilamentCountryCodeField\Forms\Components\CountryCodeSelect;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class General extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.general';

    public static function canAccess(): bool
    {
        return checkPermission('manage_settings');
    }

    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Wrench;

    protected static ?int $navigationSort = NavigationSort::GENERAL->value;

    public ?array $data = [];

    public ?array $record = [];

    public static function getNavigationLabel(): string
    {
        return __('messages.setting.general');
    }

    public function getTitle(): string
    {
        return __('messages.setting.general');
    }

    public function mount(): void
    {
        $this->record = Setting::pluck('value', 'key')->toArray();

        if (! empty($this->record['default_country_code'])) {
            $defaultCountryCode = '+'.PhoneNumberUtil::getInstance()->getCountryCodeForRegion($this->record['default_country_code']);
        }

        $this->form->fill([
            'clinic_name' => $this->record['clinic_name'] ?? null,
            'contact_no' => $this->record['contact_no'] ?? null,
            // 'default_country_code' => $defaultCountryCode ?? null,
            'email' => $this->record['email'] ?? null,
            'specialities' => $this->record['specialities'] ?? null,
            'logo' => $this->record['logo'] ?? null,
            'favicon' => $this->record['favicon'] ?? null,
            'login_bg_image' => $this->record['login_bg_image'] ?? null,
            'email_verified' => $this->record['email_verified'] ?? null,
            'currency' => $this->record['currency'] ?? null,
            'language' => $this->record['language'] ?? null,
            'recaptcha' => $this->record['recaptcha'] ?? null,
            'googleCaptchaKey' => $this->record['googleCaptchaKey'] ?? null,
            'googleCaptchaSecret' => $this->record['googleCaptchaSecret'] ?? null,
            'default_country_code' => $defaultCountryCode ?? '+91',
            'timezone' => $this->record['timezone'] ?? config('app.timezone'),
            'stripe_enable' => $this->record['stripe_enable'] ?? false,
            'stripe_key' => $this->record['stripe_key'] ?? null,
            'stripe_secret' => $this->record['stripe_secret'] ?? null,
            'paypal_enable' => $this->record['paypal_enable'] ?? false,
            'paypal_key' => $this->record['paypal_key'] ?? null,
            'paypal_secret' => $this->record['paypal_secret'] ?? null,
            'paypal_mode' => $this->record['paypal_mode'] ?? null,
            'razorpay_enable' => $this->record['razorpay_enable'] ?? false,
            'razorpay_key' => $this->record['razorpay_key'] ?? null,
            'razorpay_secret' => $this->record['razorpay_secret'] ?? null,
            'paystack_enable' => $this->record['paystack_enable'] ?? false,
            'paystack_key' => $this->record['paystack_key'] ?? null,
            'paystack_secret' => $this->record['paystack_secret'] ?? null,
            'authorize_enable' => $this->record['authorize_enable'] ?? false,
            'authorize_login_id' => $this->record['authorize_login_id'] ?? null,
            'authorize_transaction_key' => $this->record['authorize_transaction_key'] ?? null,
            'authorize_mode' => $this->record['authorize_mode'] ?? null,
            'manual_payment_enabled' => $this->record['manual_payment_enabled'] ?? false,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('messages.setting.general_details'))
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('clinic_name')
                                ->label(__('messages.setting.clinic_name').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.setting.clinic_name'))
                                ->required(),
                            PhoneInput::make('contact_no')
                                ->label(__('messages.user.contact_number').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.user.contact_number'))
                                ->required(),
                            CountryCodeSelect::make('default_country_code')
                                ->label(__('messages.setting.default_country_code').':')
                                ->inlineLabel()
                                ->required(),
                            TextInput::make('email')
                                ->label(__('messages.user.email').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.user.email'))
                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
                                ->required(),
                            Select::make('specialities')
                                ->label(__('messages.setting.specialities').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.setting.specialities'))
                                ->required()
                                ->multiple()
                                ->native(false)
                                ->options(Specialization::orderBy('name', 'asc')->pluck('name', 'id')),
                            SpatieMediaLibraryFileUpload::make('logo')
                                ->label(__('messages.setting.logo').':')
                                ->inlineLabel()
                                ->image()
                                ->imagePreviewHeight(150)
                                ->disk(config('app.media_disk'))
                                ->collection(Setting::LOGO)
                                ->model(
                                    Setting::where('key', 'logo')->first() ?? new Setting(['key' => 'logo'])
                                )
                                ->required(),
                            SpatieMediaLibraryFileUpload::make('favicon')
                                ->label(__('messages.setting.favicon').':')
                                ->inlineLabel()
                                ->image()
                                ->imagePreviewHeight(150)
                                ->disk(config('app.media_disk'))
                                ->collection(Setting::FAVICON)
                                ->model(
                                    Setting::where('key', 'favicon')->first() ?? new Setting(['key' => 'favicon'])
                                )
                                ->required(),
                            SpatieMediaLibraryFileUpload::make('login_bg_image')
                                ->label(__('messages.setting.login_bg_image') . ':')
                                ->inlineLabel()
                                ->image()
                                ->imagePreviewHeight(150)
                                ->disk(config('app.media_disk'))
                                ->collection(Setting::LOGIN_BG_IMAGE)
                                ->model(fn () => Setting::firstOrCreate(['key' => 'login_bg_image'], ['value' => '']))
                                ->required(),
                            Checkbox::make('email_verified')
                                ->label(__('messages.setting.do_not_allow_to_login_without_email_verification').':')
                                ->inlineLabel()
                                ->hintIcon('heroicon-o-question-mark-circle', tooltip: __('messages.setting.when_checkbox_disable'))
                                ->required(),

                            Select::make('timezone')
                                ->label('TimeZone:')
                                ->inlineLabel()
                                ->placeholder('Select Timezone')
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->default(config('app.timezone'))
                                ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray())
                                ->visible(fn () => isRole('admin') || isRole('clinic_admin'))
                        ]),
                    ]),

                Section::make(__('messages.setting.currency_settings'))
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('currency')
                                ->label(__('messages.setting.currency').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.setting.currency'))
                                ->options(Currency::toBase()->pluck('currency_name', 'id'))
                                ->native(false)
                                ->searchable(),
                            Select::make('language')
                                ->label(__('messages.common.default_language').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.common.default_language'))
                                ->hintIcon('heroicon-o-question-mark-circle', tooltip: __('messages.setting.default_language_suggest'))
                                ->options(User::LANGUAGES)
                                ->native(false)
                                ->searchable(),
                            Toggle::make('recaptcha')
                                ->label(__('messages.common.google_recaptcha').':')
                                ->inlineLabel()
                                ->reactive(),
                            TextInput::make('googleCaptchaKey')
                                ->label(__('messages.common.google_captcha_key').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.common.google_captcha_key'))
                                ->visible(fn (Get $get) => $get('recaptcha'))
                                ->required(fn (Get $get) => $get('recaptcha')),
                            TextInput::make('googleCaptchaSecret')
                                ->label(__('messages.common.google_captcha_secret').':')
                                ->inlineLabel()
                                ->placeholder(__('messages.common.google_captcha_secret'))
                                ->visible(fn (Get $get) => $get('recaptcha'))
                                ->required(fn (Get $get) => $get('recaptcha')),
                        ]),
                    ]),

                Section::make(__('messages.appointment.payment_method'))
                    ->schema([
                        Toggle::make('stripe_enable')
                            ->live()
                            ->label(__('messages.payment_method.stripe') . ':'),
                        Group::make()
                            ->schema([
                                TextInput::make('stripe_key')
                                    ->label(__('messages.setting.stripe_key') . ':')
                                    ->placeholder(__('messages.setting.stripe_key'))
                                    ->validationAttribute(__('messages.setting.stripe_key'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('stripe_secret')
                                    ->label(__('messages.setting.stripe_secret') . ':')
                                    ->placeholder(__('messages.setting.stripe_secret'))
                                    ->validationAttribute(__('messages.setting.stripe_secret'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                            ])->columns(2)->visible(fn (Get $get) => $get('stripe_enable')),

                        Toggle::make('paypal_enable')
                            ->live()
                            ->label(__('messages.payment_method.paypal') . ':'),
                        Group::make()
                            ->schema([
                                TextInput::make('paypal_key')
                                    ->label(__('messages.setting.paypal_client_id'))
                                    ->placeholder(__('messages.setting.paypal_client_id'))
                                    ->validationAttribute(__('messages.setting.paypal_client_id'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('paypal_secret')
                                    ->label(__('messages.setting.paypal_secret') . ':')
                                    ->placeholder(__('messages.setting.paypal_secret'))
                                    ->validationAttribute(__('messages.setting.paypal_secret'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('paypal_mode')
                                    ->required()
                                    ->validationAttribute(__('messages.setting.paypal_mode'))
                                    ->placeholder(__('messages.setting.paypal_mode'))
                                    ->label(__('messages.setting.paypal_mode') . ':')
                                    ->password()
                                    ->revealable(),
                            ])->columns(2)->visible(fn (Get $get) => $get('paypal_enable')),

                        Toggle::make('razorpay_enable')
                            ->live()
                            ->label(__('messages.payment_method.razorpay') . ':'),
                        Group::make()
                            ->schema([
                                TextInput::make('razorpay_key')
                                    ->label(__('messages.setting.razorpay_key'))
                                    ->placeholder(__('messages.setting.razorpay_key'))
                                    ->validationAttribute(__('messages.setting.razorpay_key'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('razorpay_secret')
                                    ->label(__('messages.setting.razorpay_secret') . ':')
                                    ->placeholder(__('messages.setting.razorpay_secret'))
                                    ->validationAttribute(__('messages.setting.razorpay_secret'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                            ])->columns(2)->visible(fn (Get $get) => $get('razorpay_enable')),

                        Toggle::make('paystack_enable')
                            ->live()
                            ->label(__('messages.payment_method.paystack') . ':'),
                        Group::make()
                            ->schema([
                                TextInput::make('paystack_key')
                                    ->label(__('messages.setting.paystack_public_key') . ':')
                                    ->placeholder(__('messages.setting.paystack_public_key'))
                                    ->validationAttribute(__('messages.setting.paystack_public_key'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('paystack_secret')
                                    ->label(__('messages.setting.paystack_secret_key') . ':')
                                    ->placeholder(__('messages.setting.paystack_secret_key'))
                                    ->validationAttribute(__('messages.setting.paystack_secret_key'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                            ])->columns(2)->visible(fn (Get $get) => $get('paystack_enable')),

                        Toggle::make('authorize_enable')
                            ->live()
                            ->label(__('messages.payment_method.authorize') . ':'),
                        Group::make()
                            ->schema([
                                TextInput::make('authorize_login_id')
                                    ->label(__('messages.setting.authorize_login_id') . ':')
                                    ->placeholder(__('messages.setting.authorize_login_id'))
                                    ->validationAttribute(__('messages.setting.authorize_login_id'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('authorize_transaction_key')
                                    ->label(__('messages.setting.authorize_transaction_key') . ':')
                                    ->placeholder(__('messages.setting.authorize_transaction_key'))
                                    ->validationAttribute(__('messages.setting.authorize_transaction_key'))
                                    ->required()
                                    ->password()
                                    ->revealable(),
                                TextInput::make('authorize_mode')
                                    ->required()
                                    ->validationAttribute(__('messages.setting.authorize_mode'))
                                    ->placeholder(__('messages.setting.authorize_mode'))
                                    ->label(__('messages.setting.authorize_mode') . ':')
                                    ->password()
                                    ->revealable(),
                            ])->columns(2)->visible(fn (Get $get) => $get('authorize_enable')),

                        Toggle::make('manual_payment_enabled')
                            ->label(__('messages.payment_method.manually') . ':'),

                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        try {
            if (! empty($data['default_country_code'])) {
                $phoneUtil = PhoneNumberUtil::getInstance();

                $dialCode = ltrim($data['default_country_code'], '+');

                $regions = $phoneUtil->getRegionCodesForCountryCode((int) $dialCode);

                $data['default_country_code'] = ! empty($regions)
                    ? strtolower($regions[0])
                    : null;
            }

            $logoUrl = $this->getMediaSettingUrl(Setting::LOGO);

            if (! empty($logoUrl)) {
                $data['logo'] = $logoUrl;
            }

            $faviconUrl = $this->getMediaSettingUrl(Setting::FAVICON);
            if (! empty($faviconUrl)) {
                $data['favicon'] = $faviconUrl;
            }

            $loginBgUrl = $this->getMediaSettingUrl(Setting::LOGIN_BG_IMAGE);
            if (!empty($loginBgUrl)) {
                $data['login_bg_image'] = $loginBgUrl;
            }

            // Save other settings
            foreach ($data as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                } else {
                    Setting::create(['key' => $key, 'value' => $value]);
                }
            }
            Cache::forget('app_settings');
            Artisan::call('cache:clear');
            Notification::make()
                ->success()
                ->title(__('messages.flash.setting_update'))
                ->send();
        } catch (\Exception $exception) {
            $this->validate([
                'data' => 'required|array',
            ], [
                'data.required' => $exception->getMessage(),
            ]);
        }

        $this->js('window.location.reload()');
    }

    protected function getMediaSettingUrl(string $collection): ?string
    {
        $setting = Setting::where('key', $collection)->first();

        if (! $setting) {
            return null;
        }

        $url = $setting->getFirstMediaUrl($collection);

        return ! empty($url) ? $url : null;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.user.save_changes'))
                ->action('save'),
        ];
    }
}
