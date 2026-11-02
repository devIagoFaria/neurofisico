@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.privacy_policy') }}
@endsection

@section('front-content')
    <div class="policy-page">
        <section class="policy-hero-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="policy-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-7 policy-hero-copy policy-reveal policy-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.privacy_policy') }}
                            </h5>

                            <h1 class="policy-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="policy-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3">
                                <a href="{{ route('medicalContact') }}" class="btn btn-primary policy-hero-btn">
                                    {{ __('messages.web.contact_us') }}
                                </a>
                                <a href="{{ route('medicalAppointment') }}" class="btn btn-outline-primary policy-hero-btn">
                                    {{ __('messages.web.book_an_appointment') }}
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-5 policy-reveal policy-reveal--right">
                            <div class="policy-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('web/media/illustrations/terms-1.png') }}"
                                    alt="{{ __('messages.privacy_policy') }}" class="img-fluid policy-hero-image"
                                    loading="lazy">

                                <div class="policy-hero-image-badge">
                                    <strong>{{ __('messages.web.privacy_first') }}</strong>
                                    <span>{{ __('messages.web.your_policy_details_are_outlined_below_in_a_clear_format') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="policy-hero-mini-stats">
                        <div class="policy-mini-stat policy-reveal policy-reveal--up policy-reveal--delay-1">
                            <span class="policy-mini-stat__value">{{ __('messages.web.clear') }}</span>
                            <span class="policy-mini-stat__label">{{ __('messages.web.simple_language') }}</span>
                        </div>
                        <div class="policy-mini-stat policy-reveal policy-reveal--up policy-reveal--delay-2">
                            <span class="policy-mini-stat__value">{{ __('messages.web.safe') }}</span>
                            <span class="policy-mini-stat__label">{{ __('messages.web.data_handling') }}</span>
                        </div>
                        <div class="policy-mini-stat policy-reveal policy-reveal--up policy-reveal--delay-3">
                            <span class="policy-mini-stat__value">{{ __('messages.web.trust') }}</span>
                            <span class="policy-mini-stat__label">{{ __('messages.web.patient_confidence') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="policy-content-section p-b-100">
            <div class="container">
                <div class="policy-content-shell policy-reveal policy-reveal--up">
                    <div class="policy-content-shell__header mb-4">
                        <h2 class="mb-2">{{ __('messages.privacy_policy') }}</h2>
                        <p class="mb-0">
                            {{ __('messages.web.privacy_panel_intro') }}
                        </p>
                    </div>

                    <article class="policy-article">
                        {!! $privacyPolicy['privacy_policy'] !!}
                    </article>
                </div>
            </div>
        </section>
    </div>
@endsection
