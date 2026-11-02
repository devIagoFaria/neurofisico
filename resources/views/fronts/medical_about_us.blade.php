@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.web.medical_about_us') }}
@endsection

@section('front-content')
    <div class="about-page">
        <!-- start hero section -->
        <section class="about-hero-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="about-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 about-hero-copy about-reveal about-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.about_us') }}
                            </h5>

                            <h1 class="about-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="about-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 mb-4">
                                <a href="{{ route('medicalContact') }}" class="btn btn-primary about-hero-btn">
                                    {{ __('messages.web.contact_us') }}
                                </a>

                                <a href="{{ route('medicalAppointment') }}" class="btn btn-outline-primary about-hero-btn">
                                    {{ __('messages.web.book_an_appointment') }}
                                </a>
                            </div>

                            <div class="about-hero-points">
                                <div class="about-point">
                                    <i class="fa-solid fa-truck-medical"></i>
                                    <span>{{ __('messages.web.emergency_help') }}</span>
                                </div>
                                <div class="about-point">
                                    <i class="fa-solid fa-user-doctor"></i>
                                    <span>{{ __('messages.web.qualified_doctors') }}</span>
                                </div>
                                <div class="about-point">
                                    <i class="fa-solid fa-heart-pulse"></i>
                                    <span>{{ __('messages.web.best_professionals') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 about-hero-visual about-reveal about-reveal--right">
                            <div class="about-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : getSettingValue('about_image_1') }}"
                                    alt="{{ __('messages.web.about_us') }}" class="img-fluid about-hero-image"
                                    loading="lazy">

                                <div class="about-hero-stamp">
                                    <strong>{{ getSettingValue('about_experience') }}</strong>
                                    <span>{{ __('messages.web.year_experience') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="about-hero-mini-stats">
                        <div class="about-mini-stat about-reveal about-reveal--up about-reveal--delay-1">
                            <span class="about-mini-stat__value">{{ $data['specializationsCount'] }}</span>
                            <span class="about-mini-stat__label">{{ __('messages.specializations') }}</span>
                        </div>
                        <div class="about-mini-stat about-reveal about-reveal--up about-reveal--delay-2">
                            <span class="about-mini-stat__value">{{ $data['servicesCount'] }}</span>
                            <span class="about-mini-stat__label">{{ __('messages.web.services') }}</span>
                        </div>
                        <div class="about-mini-stat about-reveal about-reveal--up about-reveal--delay-3">
                            <span class="about-mini-stat__value">{{ $data['doctorsCount'] }}</span>
                            <span class="about-mini-stat__label">{{ __('messages.doctors') }}</span>
                        </div>
                        <div class="about-mini-stat about-reveal about-reveal--up about-reveal--delay-4">
                            <span class="about-mini-stat__value">{{ $data['patientsCount'] }}</span>
                            <span class="about-mini-stat__label">{{ __('messages.web.satisfied_patient') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start story section -->
        <section class="about-story-section p-b-100">
            <div class="container">
                <div class="about-story-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 about-story-visual about-reveal about-reveal--left">
                            <div class="about-story-image-grid">
                                <div class="about-story-image-card about-story-image-card--large">
                                    <img src="{{ getSettingValue('about_image_2') }}"
                                        alt="{{ __('messages.web.about_us') }}" loading="lazy">
                                </div>
                                <div class="about-story-image-card about-story-image-card--small">
                                    <img src="{{ getSettingValue('about_image_3') }}"
                                        alt="{{ __('messages.web.about_us') }}" loading="lazy">
                                </div>
                                <div class="about-story-value">
                                    <span
                                        class="about-story-value__number">{{ getSettingValue('about_experience') }}</span>
                                    <span class="about-story-value__text">{{ __('messages.web.year_experience') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 about-story-copy about-reveal about-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.about_us') }}</h5>
                            <h2 class="about-story-title pb-2">{{ __('messages.web.our_mission') }}</h2>
                            <p class="about-story-text paragraph pb-1">
                                {{ getSettingValue('about_short_description') }}
                            </p>

                            <div class="about-highlights">
                                <div class="about-highlight-card">
                                    <i class="fa-solid fa-shield-heart"></i>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.web.patient_first_care') }}</h6>
                                        <p class="mb-0">{{ __('messages.web.patient_first_care_description') }}</p>
                                    </div>
                                </div>
                                <div class="about-highlight-card">
                                    <i class="fa-regular fa-calendar"></i>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.web.easy_booking') }}</h6>
                                        <p class="mb-0">{{ __('messages.web.easy_booking_description') }}</p>
                                    </div>
                                </div>
                                <div class="about-highlight-card">
                                    <i class="fa-solid fa-stethoscope"></i>
                                    <div>
                                        <h6 class="mb-1">{{ __('messages.web.specialist_care') }}</h6>
                                        <p class="mb-0">{{ __('messages.web.specialist_care_description') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a href="{{ route('medicalContact') }}" class="btn btn-primary about-story-btn">
                                    {{ __('messages.web.contact_us') }}
                                </a>
                                <a href="{{ route('medicalDoctors') }}" class="btn btn-outline-primary about-story-btn">
                                    {{ __('messages.web.medical_doctors') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end story section -->

        <!-- start our-doctors section -->
        <section class="about-doctors-section bg-secondary p-t-100 p-b-100">
            <div class="container">
                <div class="about-section-heading text-center mb-5">
                    <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.our_doctor') }}</h5>
                    <h2 class="mb-3">{{ __('messages.web.Meet_best_doctors') }}</h2>
                    <p class="paragraph mb-0">
                        {{ __('messages.web.about_browse_specialists_intro') }}
                    </p>
                </div>

                <div class="row justify-content-center g-4">
                    @forelse($doctors as $doctor)
                        @php
                            $specialization =
                                optional($doctor->specializations->first())->name ?? __('messages.common.n/a');
                        @endphp

                        <div class="col-xl-4 col-md-6 d-flex about-reveal about-reveal--up">
                            <div class="about-doctor-card w-100">
                                <div class="about-doctor-card__top">
                                    <span class="about-doctor-card__pulse"></span>
                                </div>

                                <div class="about-doctor-card__media">
                                    <img src="{{ $doctor->user->profile_image }}" alt="{{ $doctor->user->full_name }}"
                                        loading="lazy">
                                </div>

                                <div class="about-doctor-card__body">
                                    <div class="about-doctor-card__header">
                                        <div>
                                            <h4 class="mb-1">{{ $doctor->user->full_name }}</h4>
                                            <p class="about-doctor-card__specialization mb-0">{{ $specialization }}</p>
                                        </div>
                                    </div>

                                    <div class="about-doctor-card__social">
                                        @if (!empty($doctor->twitter_url))
                                            <a target="_blank" href="{{ $doctor->twitter_url }}" aria-label="Twitter">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        @endif
                                        @if (!empty($doctor->linkedin_url))
                                            <a target="_blank" href="{{ $doctor->linkedin_url }}" aria-label="LinkedIn">
                                                <i class="fab fa-linkedin-in"></i>
                                            </a>
                                        @endif
                                        @if (!empty($doctor->instagram_url))
                                            <a target="_blank" href="{{ $doctor->instagram_url }}"
                                                aria-label="Instagram">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        @endif
                                    </div>

                                    <a href="{{ route('doctorBookAppointment', $doctor->id) }}"
                                        class="about-doctor-card__btn btn btn-primary mt-auto">
                                        <span>{{ __('messages.web.book_an_appointment') }}</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="about-empty-state text-center">
                                <h4 class="mb-2">{{ __('messages.web.no_doctors_found') }}</h4>
                                <p class="paragraph mb-0">{{ __('messages.web.about_updated_specialist_roster') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
        <!-- end our-doctors section -->

        @include('fronts.patient_testimonial')
    </div>
@endsection
