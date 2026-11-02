@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.web.medical_appointment') }}
@endsection
@section('front-content')
    @php
        $styleCss = 'style';
    @endphp
    <div class="book-appointment-page">
        <!-- start hero section -->
        <section class="book-hero-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="book-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 book-hero-copy book-reveal book-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.book_an_appointment') }}
                            </h5>

                            <h1 class="book-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="book-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 mb-4">
                                <a href="{{ route('medicalDoctors') }}" class="btn btn-primary book-hero-btn">
                                    {{ __('messages.web.our_doctor') }}
                                </a>

                                <a href="{{ route('medicalContact') }}" class="btn btn-outline-primary book-hero-btn">
                                    {{ __('messages.web.contact_us') }}
                                </a>
                            </div>

                            <div class="book-hero-points">
                                <div class="book-point">
                                    <i class="fa-solid fa-user-doctor"></i>
                                    <span>{{ __('messages.web.pick_your_specialist') }}</span>
                                </div>
                                <div class="book-point">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    <span>{{ __('messages.web.select_an_open_slot') }}</span>
                                </div>
                                <div class="book-point">
                                    <i class="fa-solid fa-shield-heart"></i>
                                    <span>{{ __('messages.web.confirm_securely') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 book-hero-visual book-reveal book-reveal--right">
                            <div class="book-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('assets/front/images/appointment.png') }}"
                                    alt="{{ __('messages.web.book_appointment') }}" class="img-fluid book-hero-image"
                                    loading="lazy">

                                <div class="book-hero-stamp">
                                    <strong>{{ __('messages.web.fast') }}</strong>
                                    <span>{{ __('messages.web.easy_booking_flow') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="book-hero-mini-stats">
                        <div class="book-mini-stat book-reveal book-reveal--up book-reveal--delay-1">
                            <span class="book-mini-stat__value">1</span>
                            <span class="book-mini-stat__label">{{ __('messages.web.select_doctor') }}</span>
                        </div>
                        <div class="book-mini-stat book-reveal book-reveal--up book-reveal--delay-2">
                            <span class="book-mini-stat__value">2</span>
                            <span class="book-mini-stat__label">{{ __('messages.web.pick_service') }}</span>
                        </div>
                        <div class="book-mini-stat book-reveal book-reveal--up book-reveal--delay-3">
                            <span class="book-mini-stat__value">3</span>
                            <span class="book-mini-stat__label">{{ __('messages.web.choose_slot') }}</span>
                        </div>
                        <div class="book-mini-stat book-reveal book-reveal--up book-reveal--delay-4">
                            <span class="book-mini-stat__value">4</span>
                            <span class="book-mini-stat__label">{{ __('messages.web.confirm_booking_step') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start book appointment section -->
        <section class="book-appointment-section p-b-100">
            <div class="container">
                <div class="book-appointment-layout">
                    <aside class="book-appointment-aside book-reveal book-reveal--left">
                        <div class="book-appointment-aside__inner">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.book_an_appointment') }}
                            </h5>
                            <h2 class="book-appointment-aside__title mb-3">
                                {{ __('messages.web.booking_intro') }}
                            </h2>
                            <p class="book-appointment-aside__text mb-4">
                                {{ __('messages.web.booking_page_intro') }}
                            </p>

                            <div class="book-appointment-aside__list">
                                <div class="book-appointment-aside__item">
                                    <i class="fa-solid fa-user-doctor"></i>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.web.choose_a_doctor') }}</h6>
                                        <p class="mb-0">
                                            {{ __('messages.web.browse_the_available_specialists_for_your_appointment') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="book-appointment-aside__item">
                                    <i class="fa-solid fa-clock"></i>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.web.see_live_slots') }}</h6>
                                        <p class="mb-0">
                                            {{ __('messages.web.pick_from_the_current_availability_and_avoid_back_and_forth') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="book-appointment-aside__item">
                                    <i class="fa-solid fa-credit-card"></i>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.web.confirm_payment') }}</h6>
                                        <p class="mb-0">
                                            {{ __('messages.web.finish_the_booking_with_the_payment_method_you_prefer') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="book-appointment-aside__note">
                                <strong>{{ __('messages.web.tip') }}</strong>
                                <span>{{ __('messages.web.choose_the_doctor_first_to_unlock_the_available_services_and_dates') }}</span>
                            </div>
                        </div>
                    </aside>

                    <div class="appointment-form form-wraper book-reveal book-reveal--up">
                        <form class="book-appointment-form bg-white rounded-20 box-shadow" id="frontAppointmentBook"
                            action="{{ route('front.appointment.book') }}" method="post">
                            @csrf
                            @include('fronts.common.book_appointment')
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- end book appointment section -->
    </div>
@endsection
