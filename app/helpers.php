<?php

use App\Models\Appointment;
use App\Models\Currency;
use App\Models\Doctor;
use App\Models\MedicineBill;
use App\Models\PaymentGateway;
use App\Models\PurchaseMedicine;
use App\Models\Setting;
use App\Models\User;
use App\Models\ZoomOAuth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Gerardojbaez\Money\Exceptions\CurrencyException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Stripe\Stripe;
use ToneGabes\Filament\Icons\Enums\Phosphor;

if (! function_exists('getLogInUser')) {
    function getLogInUser()
    {
        return Auth::user();
    }
}

if (! function_exists('getLogInUserId')) {
    /**
     * @return int
     */
    function getLogInUserId()
    {
        return Auth::user()->id;
    }
}

if (! function_exists('getAppName')) {
    /**
     * @return mixed
     */
    function getAppName()
    {
        return getSettingValue('clinic_name') ?? 'Infy-Care';
    }
}

if (! function_exists('getAppLogo')) {
    /**
     * @return mixed
     */
    function getAppLogo()
    {
        return getSettingValue('logo') ?? asset('neurofisico-logo.png');
    }
}

if (! function_exists('getAppFavicon')) {
    /**
     * @return mixed
     */
    function getAppFavicon()
    {
        return getSettingValue('favicon') ?? asset('neurofisico-logo.png');
    }
}

if (! function_exists('getLoginBgImageUrl')) {
    /**
     * @return mixed
     */
    function getLoginBgImageUrl()
    {
        $loginBgImage = getSettingValue('login_bg_image');

        return ! empty($loginBgImage) ? $loginBgImage : asset('assets/front/images/default-login-bg.png');
    }
}

if (! function_exists('getPdfImagePath')) {
    function getPdfImagePath(?string $pathOrUrl, ?string $fallbackPath = null): ?string
    {
        if (filled($pathOrUrl) && file_exists($pathOrUrl)) {
            return $pathOrUrl;
        }

        if (filled($pathOrUrl)) {
            $urlPath = parse_url($pathOrUrl, PHP_URL_PATH);

            if (filled($urlPath)) {
                $candidatePath = public_path(ltrim($urlPath, '/'));

                if (file_exists($candidatePath)) {
                    return $candidatePath;
                }
            }
        }

        if (filled($fallbackPath) && file_exists($fallbackPath)) {
            return $fallbackPath;
        }

        return null;
    }
}

if (! function_exists('getPdfImageBase64')) {
    function getPdfImageBase64(?string $pathOrUrl, ?string $fallbackPath = null): ?string
    {
        $imagePath = getPdfImagePath($pathOrUrl, $fallbackPath);

        if (! $imagePath) {
            return null;
        }

        return base64_encode(file_get_contents($imagePath));
    }
}

if (! function_exists('getPublicFileUrl')) {
    function getPublicFileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}

if (! function_exists('getFileUploadStatePath')) {
    function getFileUploadStatePath(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return ltrim($value, '/');
        }

        $urlPath = parse_url($value, PHP_URL_PATH);

        if (blank($urlPath)) {
            return null;
        }

        $uploadsPrefix = '/uploads/';

        if (str_contains($urlPath, $uploadsPrefix)) {
            return ltrim(substr($urlPath, strpos($urlPath, $uploadsPrefix) + strlen($uploadsPrefix)), '/');
        }

        return ltrim($urlPath, '/');
    }
}

if (! function_exists('isRole')) {
    function isRole(string $role)
    {

        if (Auth::user() && Auth::user()->hasRole($role)) {

            return true;
        }

        return false;
    }
}

if (! function_exists('checkPermission')) {
    function checkPermission($permission)
    {
        /** @var \App\Models\User $user */
        $user = getLogInUser();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('clinic_admin')) {
            return true;
        }

        $doctorPermissions = [
            'manage_appointments',
            'manage_transactions',
        ];

        $patientPermissions = [
            'manage_appointments',
            'manage_patient_visits',
            'manage_transactions',
        ];

        if ($user->hasRole('doctor')) {

            if (in_array($permission, $doctorPermissions)) {
                return true;
            }

            return $user->hasPermissionTo($permission);
        }

        if ($user->hasRole('patient')) {

            if (in_array($permission, $patientPermissions)) {
                return true;
            }
            return $user->hasPermissionTo($permission);
        }

        return $user->hasPermissionTo($permission);
    }
}

if (! function_exists('notify')) {
    function notify($title, $body = null, $type = 'success')
    {
        return Notification::make()
            ->{$type}()
            ->title($title)
            ->body($body)
            ->send();
    }
}

function backActionResource($url = null): Action
{
    return Action::make('back')
        ->label(__('messages.common.back'))
        ->icon(Phosphor::ArrowLeft)
        ->outlined()
        ->url($url ?? 'javascript:history.back()');
}

function role(): ?string
{
    return Auth::user()?->role_name;
}

function formattedDate($format = null)
{
    return $format ?? 'd M Y G:i';
}

function datePickerFormat($format = null)
{
    return $format ?? 'd M Y';
}

function rating($count)
{
    $count = max(0, min(5, intval($count)));

    $filled = str_repeat('★ ', $count);
    $unfilled = str_repeat('★ ', 5 - $count);

    return new HtmlString(
        '<span style="color:#f5c518;padding:0;">'.$filled.'</span>'.
            '<span style="color:#f5c518; -webkit-text-stroke: 1px #f5c518; color: transparent; padding:0;">'.$unfilled.'</span>'
    );
}

function isCaptchaEnabled(): bool
{
    $enabled = filter_var(getSettingValue('recaptcha'), FILTER_VALIDATE_BOOLEAN);
    $siteKey = getSettingValue('googleCaptchaKey');
    $secretKey = getSettingValue('googleCaptchaSecret');

    return $enabled && filled($siteKey) && filled($secretKey);
}

if (! function_exists('getSlotByGap')) {

    function getSlotByGap($startTime, $endTime, $gap = '15')
    {
        $period = new CarbonPeriod(
            $startTime,
            $gap.' minutes',
            $endTime
        );
        $slots = [];
        foreach ($period as $item) {
            $time12 = $item->format('G:i');
            $slots[$time12] = $time12;
        }

        return $slots;
    }
}

function getDashboardURL()
{
    $user = getLogInUser();
    if (! $user) {
        return route('filament.auth.auth.login');
    }

    if ($user->hasRole('clinic_admin')) {
        return route('filament.admin.pages.dashboard');
    }

    if ($user->hasRole('doctor')) {
        return route('filament.doctor.pages.dashboard');
    }

    if ($user->hasRole('patient')) {
        return route('filament.patient.pages.dashboard');
    }

    return route('filament.admin.pages.dashboard');
}

if (! function_exists('getAllPaymentStatus')) {
    function getAllPaymentStatus()
    {
        $methods = [];
        if (getSettingValue('manual_payment_enabled')) {
            $methods[Appointment::MANUALLY] = __('messages.payment_method.manually');
        }
        if (getSettingValue('stripe_enable')) {
            $methods[Appointment::STRIPE] = __('messages.payment_method.stripe');
        }
        if (getSettingValue('paystack_enable')) {
            $methods[Appointment::PAYSTACK] = __('messages.payment_method.paystack');
        }
        if (getSettingValue('paypal_enable')) {
            $methods[Appointment::PAYPAL] = __('messages.payment_method.paypal');
        }
        if (getSettingValue('razorpay_enable')) {
            $methods[Appointment::RAZORPAY] = __('messages.payment_method.razorpay');
        }
        if (getSettingValue('authorize_enable')) {
            $methods[Appointment::AUTHORIZE] = __('messages.payment_method.authorize');
        }

        return $methods;
    }
}

if (! function_exists('getCurrency')) {
    function getCurrency()
    {
        static $currency = null;

        if ($currency !== null) {
            return $currency;
        }

        $currencyId = getSettingValue('currency');

        if (! $currencyId) {
            return null;
        }

        return $currency = Currency::find($currencyId);
    }
}

if (! function_exists('getCurrencyIcon')) {
    function getCurrencyIcon()
    {
        return getCurrency()?->currency_icon ?? 'R$';
    }
}

if (! function_exists('getCurrencyCode')) {
    function getCurrencyCode()
    {
        return getCurrency()?->currency_code ?? 'USD';
    }
}

if (! function_exists('zeroDecimalCurrencies')) {

    function zeroDecimalCurrencies(): array
    {
        return [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'UGX',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ];
    }
}

if (! function_exists('setStripeApiKey')) {

    function setStripeApiKey()
    {
        Stripe::setApiKey(getSettingValue('stripe_secret'));
    }
}

if (! function_exists('paystackCurrencySupports')) {
    function paystackCurrencySupports()
    {
        $paystack = ['NGN', 'USD', 'GHS', 'ZAR', 'KES'];

        if (! in_array(strtoupper(getCurrencyCode()), $paystack)) {
            return false;
        }

        return true;
    }
}

if (! function_exists('generateUniquePurchaseNumber')) {

    function generateUniquePurchaseNumber()
    {
        do {
            $code = random_int(100000, 999999);
        } while (PurchaseMedicine::where('purchase_no', '=', $code)->first());

        return $code;
    }
}

if (! function_exists('generateUniqueBillNumber')) {
    function generateUniqueBillNumber()
    {
        do {
            $code = random_int(1000, 9999);
        } while (MedicineBill::where('bill_number', '=', $code)->first());

        return $code;
    }
}

function getUserImageInitial($userId, $name)
{
    return getAvatarUrl()."?name=$name&size=100&rounded=true&color=fff&background=".getRandomColor($userId);
}

function getAvatarUrl()
{
    return 'https://ui-avatars.com/api/';
}

function getRandomColor($userId)
{
    $colors = ['329af0', 'fc6369', 'ffaa2e', '42c9af', '7d68f0'];
    $index = $userId % 5;

    return $colors[$index];
}

if (! function_exists('getBadgeColor')) {

    /**
     * @return string
     */
    function getBadgeColor($index)
    {
        $colors = [
            'primary',
            'danger',
            'success',
            'info',
            'warning',
            'gray',
        ];

        $index = $index % 6;
        if (Auth::user()->dark_mode) {
            array_splice($colors, 5, 1);
            array_push($colors, 'bg-white');
        }

        return $colors[$index];
    }
}

if (! function_exists('getSettingValue')) {
    function getSettingValue($key)
    {
        static $settings = null;

        try {
            if ($settings === null) {
                $settings = Setting::pluck('value', 'key')->toArray();
            }

            return $settings[$key] ?? null;

        } catch (Throwable $e) {
            return null;
        }
    }
}

if (! function_exists('getSchedulesTimingSlot')) {

    function getSchedulesTimingSlot()
    {
        $period = new CarbonPeriod('00:00', '15 minutes', '24:00'); // for create use 24 hours format later change format
        $slots = [];
        foreach ($period as $item) {
            $slots[$item->format('g:i A')] = $item->format('g:i A');
        }

        return $slots;
    }
}

if (! function_exists('getAmountToWord')) {

    function getAmountToWord(float $amount): string
    {
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2);
        $count_length = strlen($num);
        $x = 0;
        $string = [];
        $change_words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety',
        ];
        $here_digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount = floor($num % $get_divider);
            $num = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount) {
                $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string[] = ($amount < 21) ? $change_words[$amount].' '.$here_digits[$counter].$add_plural.'
       '.$amt_hundred : $change_words[floor($amount / 10)].' '.$change_words[$amount % 10].'
       '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
            } else {
                $string[] = null;
            }
        }
        $implode_to_Rupees = implode('', array_reverse($string));
        $get_paise = ($amount_after_decimal > 0) ? 'And '.($change_words[$amount_after_decimal / 10].'
   '.$change_words[$amount_after_decimal % 10]).' Paise' : '';

        return ($implode_to_Rupees ? $implode_to_Rupees.getCurrencyCode() : '').$get_paise;
    }
}

if (! function_exists('getCurrencyFormat')) {
    function getCurrencyFormat($currencyCode, $amount): string
    {
        $currencyCode = strtoupper((string) ($currencyCode ?: getCurrencyCode()));

        if (! array_key_exists($currencyCode, Gerardojbaez\Money\Currency::getAllCurrencies())) {
            return number_format((float) $amount, 2);
        }

        try {
            return moneyFormat($amount, $currencyCode);
        } catch (CurrencyException $e) {
            return number_format((float) $amount, 2);
        }
    }
}

if (! function_exists('setEmailLowerCase')) {

    /**
     * @return string
     */
    function setEmailLowerCase($email)
    {
        return strtolower($email);
    }
}

if (! function_exists('getPaymentGateway')) {

    /**
     * @return string[]
     */
    function getPaymentGateway()
    {

        $paymentGateway = Appointment::PAYMENT_GATEWAY;
        $selectedPaymentGateway = PaymentGateway::pluck('payment_gateway')->toArray();

        $paymentGatewayToReturn = array_intersect($paymentGateway, $selectedPaymentGateway);

        return $paymentGatewayToReturn;
    }
}

if (! function_exists('paypalCurrencySupports')) {
    function paypalCurrencySupports(): bool
    {
        $paypal = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'USD', 'SGD', 'SEK', 'CHF', 'THB'];

        $currency = trim(strtoupper((string) getCurrencyCode()));

        return in_array($currency, $paypal, true);
    }

}

if (! function_exists('razorpayCurrencySupports')) {
    function razorpayCurrencySupports()
    {
        $rozarpay = [
            'AED',
            'ALL',
            'AMD',
            'ARS',
            'AUD',
            'AWG',
            'BBD',
            'BDT',
            'BMD',
            'BND',
            'BOB',
            'BSD',
            'BWP',
            'BZD',
            'CAD',
            'CHF',
            'CNY',
            'COP',
            'CRC',
            'CUP',
            'CZK',
            'DKK',
            'DOP',
            'DZD',
            'EGP',
            'ETB',
            'EUR',
            'FJD',
            'GBP',
            'GHS',
            'GIP',
            'GMD',
            'GTQ',
            'GYD',
            'HKD',
            'HNL',
            'HRK',
            'HTG',
            'HUF',
            'IDR',
            'ILS',
            'INR',
            'JMD',
            'KES',
            'KGS',
            'KHR',
            'KYD',
            'KZT',
            'LAK',
            'LKR',
            'LRD',
            'LSL',
            'MAD',
            'MDL',
            'MKD',
            'MMK',
            'MNT',
            'MOP',
            'MUR',
            'MVR',
            'MWK',
            'MXN',
            'MYR',
            'NAD',
            'NGN',
            'NIO',
            'NOK',
            'NPR',
            'NZD',
            'PEN',
            'PGK',
            'PHP',
            'PKR',
            'QAR',
            'RUB',
            'SAR',
            'SCR',
            'SEK',
            'SGD',
            'SLL',
            'SOS',
            'SSP',
            'SVC',
            'SZL',
            'THB',
            'TTD',
            'TZS',
            'USD',
            'UYU',
            'UZS',
            'YER',
            'ZAR',
            'TRY',
        ];

        if (! in_array(strtoupper(getCurrencyCode()), $rozarpay)) {
            return false;
        }

        return true;
    }
}

if (! function_exists('isZoomTokenExpire')) {

    function isZoomTokenExpire()
    {

        $isExpired = false;
        $zoomOAuth = ZoomOAuth::where('user_id', Auth::id())->first();
        $currentTime = Carbon::now();

        $isExpired = is_null($zoomOAuth) == true ? true : $isExpired;

        if (! is_null($zoomOAuth) && $zoomOAuth->updated_at < $currentTime->subMinutes(57)) {
            $isExpired = true;
        }

        return $isExpired;
    }
}

if (! function_exists('getGoogleJsonFilePath')) {
    function getGoogleJsonFilePath()
    {
        $googleJsonFilePath = Doctor::whereUserId(Auth::id())->value('google_json_file_path');

        if (! empty($googleJsonFilePath)) {
            return $googleJsonFilePath;
        }

        return null;
    }
}

if (! function_exists('checkLanguageSession')) {
    function checkLanguageSession()
    {
        if (Session::has('languageName')) {
            return Session::get('languageName');
        } else {
            return getSettingValue('language');
        }

        return 'pt';
    }
}

// if (! function_exists('getCurrentLanguageName')) {
//     function getCurrentLanguageName()
//     {
//         return User::LANGUAGES[checkLanguageSession()] ?? 'English';
//     }
// }

// if (! function_exists('getUserLanguages')) {
//     function getUserLanguages()
//     {
//         $language = User::LANGUAGES;
//         asort($language);

//         return $language;
//     }
// }

if (! function_exists('authorizedCurrencySupports')) {
    function authorizedCurrencySupports()
    {
        $authorized = ['USD', 'CAD', 'GBP', 'EUR', 'CHF', 'DKK', 'NOK', 'PLN', 'SEK'];

        if (! in_array(strtoupper(getCurrencyCode()), $authorized)) {
            return false;
        }

        return true;
    }
}

if (! function_exists('getMonth')) {
    function getMonth()
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sesp',
            10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        return $months;
    }
}
