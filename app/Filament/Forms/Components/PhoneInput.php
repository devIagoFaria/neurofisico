<?php

namespace App\Filament\Forms\Components;

use App\Models\Setting;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput as BasePhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class PhoneInput extends BasePhoneInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $country = Setting::where('key', 'default_country_code')
            ->value('value') ?? 'in';

        $country = strtolower($country);

        $this
            ->initialCountry($country)
            ->defaultCountry(strtoupper($country))
            ->validateFor('INTERNATIONAL')
            ->inputNumberFormat(PhoneInputNumberType::E164)
            ->formatAsYouType(true)
            ->disableLookup()
            ->required();
    }
}