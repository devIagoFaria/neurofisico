@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.web.medical_doctors') }}
@endsection

@section('front-content')
    <div class="our-team-page">
        <!-- start hero section -->
        <section class="hero-content-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="team-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 team-hero-copy team-reveal team-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.our_team') }}
                            </h5>

                            <h1 class="team-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="team-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 mb-4">
                                <a href="{{ route('medicalAppointment') }}" class="btn btn-primary team-hero-btn">
                                    {{ __('messages.web.book_an_appointment') }}
                                </a>

                                <a href="{{ route('medicalContact') }}" class="btn btn-outline-primary team-hero-btn">
                                    {{ __('messages.web.contact_us') }}
                                </a>
                            </div>

                            <div class="team-hero-points">
                                <div class="team-point">
                                    <i class="fa-solid fa-stethoscope"></i>
                                    <span>{{ __('messages.web.specialist_led_care') }}</span>
                                </div>
                                <div class="team-point">
                                    <i class="fa-solid fa-star"></i>
                                    <span>{{ __('messages.web.trusted_reviews') }}</span>
                                </div>
                                <div class="team-point">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    <span>{{ __('messages.web.easy_booking') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 team-hero-visual team-reveal team-reveal--right">
                            <div class="team-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('assets/front/images/team/out-team.png') }}"
                                    alt="{{ __('messages.web.medical_doctors') }}" class="img-fluid team-hero-image"
                                    loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start stats section -->
        <section class="team-stats-section pb-5">
            <div class="container">
                <div class="team-stats-card">
                    <div class="row g-0 text-center">
                        <div class="col-md-4 team-stats-item team-reveal team-reveal--up team-reveal--delay-1">
                            <h3>{{ $doctorStats['doctors_count'] }}+</h3>
                            <p>{{ __('messages.web.expert_doctors') }}</p>
                        </div>
                        <div class="col-md-4 team-stats-item team-reveal team-reveal--up team-reveal--delay-2">
                            <h3>{{ $doctorStats['specializations_count'] }}+</h3>
                            <p>{{ __('messages.specializations') }}</p>
                        </div>
                        <div class="col-md-4 team-stats-item team-reveal team-reveal--up team-reveal--delay-3">
                            <h3>{{ $doctorStats['reviews_count'] }}+</h3>
                            <p>{{ __('messages.web.patient_reviews') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end stats section -->

        <!-- start our-team section -->
        <section class="our-team-section bg-secondary p-t-100 p-b-100">
            <div class="container">
                <div class="team-section-heading text-center mb-5">
                    <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.our_doctor') }}</h5>
                    <h2 class="mb-3">{{ __('messages.web.Meet_best_doctors') }}</h2>
                    <p class="paragraph mb-0">
                        {{ __('messages.web.doctors_browse_intro') }}
                    </p>
                </div>

                <div class="row justify-content-center g-4">
                    @forelse($doctors as $doctor)
                        @php
                            $specialization =
                                optional($doctor->specializations->first())->name ?? __('messages.common.n/a');
                            $experience = filled($doctor->experience)
                                ? $doctor->experience . ' ' . __('messages.common.years')
                                : __('messages.common.n/a');
                            $reviewCount = $doctor->reviews_count ?? 0;
                            $averageReview = round((float) ($doctor->reviews_avg_rating ?? 0), 1);
                            $reviewStars = (int) round($averageReview);
                        @endphp

                        <div class="col-xl-4 col-md-6 d-flex team-reveal team-reveal--up">
                            <div class="doctor-card w-100">
                                <div class="doctor-card__top">
                                    <span class="doctor-card__pulse"></span>
                                </div>

                                <div class="doctor-card__media">
                                    <img src="{{ $doctor->user->profile_image }}" alt="{{ $doctor->user->full_name }}"
                                        loading="lazy">
                                </div>

                                <div class="doctor-card__body">
                                    <div class="doctor-card__header">
                                        <div>
                                            <h4 class="mb-1">{{ $doctor->user->full_name }}</h4>
                                            <p class="doctor-card__specialization mb-0">{{ $specialization }}</p>
                                        </div>
                                    </div>

                                    <div class="doctor-card__rating">
                                        <div class="doctor-card__stars">
                                            {!! rating($reviewStars) !!}
                                        </div>
                                        <span>{{ number_format($averageReview, 1) }} ({{ $reviewCount }} reviews)</span>
                                    </div>

                                    <div class="doctor-card__social">
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
                                            <a target="_blank" href="{{ $doctor->instagram_url }}" aria-label="Instagram">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        @endif
                                    </div>

                                    <a href="{{ route('doctorBookAppointment', $doctor->id) }}"
                                        class="doctor-card__btn btn btn-primary mt-auto">
                                        <span>{{ __('messages.web.book_an_appointment') }}</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="doctor-empty-state text-center">
                                <h4 class="mb-2">{{ __('messages.web.no_doctors_found') }}</h4>
                                <p class="paragraph mb-0">{{ __('messages.web.about_updated_specialist_roster') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
        <!-- end our-team section -->
    </div>
@endsection
