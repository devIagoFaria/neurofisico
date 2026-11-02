@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.web.medical_contact') }}
@endsection

@section('front-content')
    @php
        $styleCss = 'style';
    @endphp

    <div class="contact-page">
        <!-- start hero section -->
        <section class="contact-hero-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="contact-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 contact-hero-copy contact-reveal contact-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.contact_us') }}
                            </h5>

                            <h1 class="contact-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="contact-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 mb-4">
                                <a href="{{ route('medicalAppointment') }}" class="btn btn-primary contact-hero-btn">
                                    {{ __('messages.web.book_an_appointment') }}
                                </a>

                                <a href="#contact-form" class="btn btn-outline-primary contact-hero-btn">
                                    {{ __('messages.web.send_message') }}
                                </a>
                            </div>

                            <div class="contact-hero-points">
                                <div class="contact-point">
                                    <i class="fa-solid fa-phone-volume"></i>
                                    <span>{{ __('messages.web.quick_phone_support') }}</span>
                                </div>
                                <div class="contact-point">
                                    <i class="fa-solid fa-envelope-open-text"></i>
                                    <span>{{ __('messages.web.fast_email_replies') }}</span>
                                </div>
                                <div class="contact-point">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>{{ __('messages.web.clear_location_details') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 contact-hero-visual contact-reveal contact-reveal--right">
                            <div class="contact-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('assets/front/images/banner/contact-us.png') }}"
                                    alt="{{ __('messages.web.contact_us') }}" class="img-fluid contact-hero-image"
                                    loading="lazy">

                                <div class="contact-hero-stamp">
                                    <strong>{{ __('messages.web.support') }}</strong>
                                    <span>{{ __('messages.web.contact_us_for_any_information') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-hero-mini-stats">
                        <div class="contact-mini-stat contact-reveal contact-reveal--up contact-reveal--delay-1">
                            <span class="contact-mini-stat__value">{{ __('messages.web.247_care') }}</span>
                            <span class="contact-mini-stat__label">{{ __('messages.web.inquiry_support') }}</span>
                        </div>
                        <div class="contact-mini-stat contact-reveal contact-reveal--up contact-reveal--delay-2">
                            <span class="contact-mini-stat__value">{{ __('messages.web.fast') }}</span>
                            <span class="contact-mini-stat__label">{{ __('messages.web.response_time') }}</span>
                        </div>
                        <div class="contact-mini-stat contact-reveal contact-reveal--up contact-reveal--delay-3">
                            <span class="contact-mini-stat__value">{{ __('messages.web.easy_booking') }}</span>
                            <span class="contact-mini-stat__label">{{ __('messages.web.appointment_access') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start contact section -->
        <section id="contact-form" class="contact-section p-b-100">
            <div class="container">
                <div class="contact-form-shell">
                    <div class="row g-0">
                        <div class="col-lg-4 contact-info-panel contact-reveal contact-reveal--left">
                            <div class="contact-info-panel__inner">
                                <h4 class="text-white mb-3">
                                    {{ __('messages.web.contact_us_for_any_information') }}
                                </h4>
                                <p class="contact-info-panel__text mb-4">
                                    {{ __('messages.web.contact_panel_intro') }}
                                </p>

                                <div class="contact-info-list">
                                    <div class="contact-info-item">
                                        <span class="contact-info-item__icon">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-1">{{ __('messages.web.location') }}</h5>
                                            <p class="mb-0">{{ getSettingValue('address_one') }}</p>
                                        </div>
                                    </div>

                                    <div class="contact-info-item">
                                        <span class="contact-info-item__icon">
                                            <i class="fa-solid fa-envelope"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-1">{{ __('messages.web.email') }}</h5>
                                            <a href="mailto:{{ getSettingValue('email') }}">
                                                {{ getSettingValue('email') }}
                                            </a>
                                        </div>
                                    </div>

                                    <div class="contact-info-item">
                                        <span class="contact-info-item__icon">
                                            <i class="fa-solid fa-phone"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-1">{{ __('messages.web.phone') }}</h5>
                                            <a
                                                href="tel:+{{ getSettingValue('region_code') }}{{ getSettingValue('contact_no') }}">
                                                +{{ ltrim(getSettingValue('region_code'), '+') }}{{ preg_replace('/\s+/', '', getSettingValue('contact_no')) }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 contact-form-panel contact-reveal contact-reveal--up">
                            <form id="enquiryForm" action="{{ route('enquiries.store') }}" class="contact-form ajax-form"
                                method="POST">
                                @csrf
                                <div class="ajax-message"></div>

                                <div class="contact-form-panel__header mb-4">
                                    <h3 class="mb-2">{{ __('messages.web.send_message') }}</h3>
                                    <p class="mb-0">
                                        {{ __('messages.web.contact_form_intro') }}
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <input type="text" name="name" id="name" class="form-control"
                                                placeholder="{{ __('messages.web.name') }}" value="{{ old('name') }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <input type="email" name="email" id="email" class="form-control"
                                                placeholder="{{ __('messages.web.email') }}" value="{{ old('email') }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <input type="tel" name="phone" class="form-control"
                                                placeholder="{{ __('messages.web.phone') }}"
                                                onkeyup='if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")'>
                                            <input type="hidden" name="region_code" id="prefix_code">
                                            <span id="valid-msg" class="hide">✓ &nbsp;
                                                {{ __('messages.valid_number') }}</span>
                                            <span id="error-msg" class="hide"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <input type="text" name="subject" id="subject" class="form-control"
                                                placeholder="{{ __('messages.web.subject') }}" maxlength="121" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="contact-form__input-block">
                                            <textarea name="message" id="message" class="form-control form-textarea"
                                                placeholder="{{ __('messages.web.message') }}" required></textarea>
                                        </div>
                                    </div>
                                    @if (config('app.google_recaptcha_site_key'))
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <div class="g-recaptcha"
                                                    data-sitekey="{{ config('app.google_recaptcha_site_key') }}"
                                                    data-callback="verifyRecaptchaCallback"
                                                    data-expired-callback="expiredRecaptchaCallback"></div>
                                                <input class="form-control d-none" {{ $styleCss }}="display:none;"
                                                    name="gre_captcha" data-recaptcha="true"
                                                    data-error="Please complete the Captcha">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-lg-12 d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary contact-submit-btn" id="submitBtn">
                                            {{ __('messages.web.send_message') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end contact section -->

        <!-- start information section -->
        <section class="information-section p-b-100">
            <div class="container">
                <div class="information-section__heading text-center p-t-100 mb-5">
                    <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.contact_us') }}</h5>
                    <h2 class="mb-3">{{ __('messages.web.contact_us_for_any_information') }}</h2>
                    <p class="paragraph mb-0">
                        Choose the best way to reach us and we’ll point you in the right direction.
                    </p>
                </div>

                <div class="row justify-content-center g-4">
                    <div class="col-xl-4 col-md-6 d-flex contact-reveal contact-reveal--up contact-reveal--delay-1">
                        <div class="contact-info-card w-100">
                            <div class="contact-info-card__icon">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <h4 class="mb-3">{{ __('messages.user.contact_number') }}</h4>
                            <a href="tel:+{{ getSettingValue('region_code') }}{{ getSettingValue('contact_no') }}">
                                +{{ ltrim(getSettingValue('region_code'), '+') }}{{ preg_replace('/\s+/', '', getSettingValue('contact_no')) }}
                            </a>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 d-flex contact-reveal contact-reveal--up contact-reveal--delay-2">
                        <div class="contact-info-card w-100">
                            <div class="contact-info-card__icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <h4 class="mb-3">{{ __('messages.web.email_address') }}</h4>
                            <a href="mailto:{{ getSettingValue('email') }}">
                                {{ getSettingValue('email') }}
                            </a>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 d-flex contact-reveal contact-reveal--up contact-reveal--delay-3">
                        <div class="contact-info-card w-100">
                            <div class="contact-info-card__icon">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <h4 class="mb-3">{{ __('messages.setting.address') }}</h4>
                            <p class="paragraph mb-0">
                                {{ getSettingValue('address_one') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end information section -->
    </div>
@endsection
