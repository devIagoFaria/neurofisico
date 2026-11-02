@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.web.medical_services') }}
@endsection

@section('front-content')
    <div class="services-page">
        <!-- start hero section -->
        <section class="services-hero-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="services-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 services-hero-copy services-reveal services-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.services') }}
                            </h5>

                            <h1 class="services-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="services-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 mb-4">
                                <a href="{{ route('medicalAppointment') }}" class="btn btn-primary services-hero-btn">
                                    {{ __('messages.web.book_an_appointment') }}
                                </a>

                                <a href="#services-grid" class="btn btn-outline-primary services-hero-btn">
                                    {{ __('messages.web.services_explore_link') }}
                                </a>
                            </div>

                            <div class="services-hero-points">
                                <div class="services-point">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>{{ __('messages.web.specialist_backed_care') }}</span>
                                </div>
                                <div class="services-point">
                                    <i class="fa-solid fa-bolt"></i>
                                    <span>{{ __('messages.web.quick_scheduling') }}</span>
                                </div>
                                <div class="services-point">
                                    <i class="fa-solid fa-shield-heart"></i>
                                    <span>{{ __('messages.web.patient_first_support') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 services-hero-visual services-reveal services-reveal--right">
                            <div class="services-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('assets/front/images/services_images/services-hero-img.png') }}"
                                    alt="{{ __('messages.web.services') }}" class="img-fluid services-hero-image"
                                    loading="lazy">
                            </div>
                        </div>
                    </div>

                    <div class="services-hero-mini-stats">
                        <div class="services-mini-stat services-reveal services-reveal--up services-reveal--delay-1">
                            <span class="services-mini-stat__value">{{ $serviceCategories->count() }}+</span>
                            <span class="services-mini-stat__label">{{ __('messages.web.service_categories') }}</span>
                        </div>
                        <div class="services-mini-stat services-reveal services-reveal--up services-reveal--delay-2">
                            <span class="services-mini-stat__value">{{ min(3, $services->count()) }}+</span>
                            <span class="services-mini-stat__label">{{ __('messages.web.highlighted_services') }}</span>
                        </div>
                        <div class="services-mini-stat services-reveal services-reveal--up services-reveal--delay-3">
                            <span class="services-mini-stat__value">{{ $data['doctorsCount'] }}+</span>
                            <span class="services-mini-stat__label">{{ __('messages.web.doctors_supporting_care') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start services grid -->
        <section id="services-grid" class="services-section p-b-100 p-t-100">
            <div class="container ">
                <div class="services-section__heading text-center mb-5">
                    <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.services') }}</h5>
                    <h2 class="mb-3">{{ __('messages.web.what_we_provide') }}</h2>
                    <p class="paragraph mb-0">
                        {{ __('messages.web.services_intro') }}
                    </p>
                </div>

                <div class="row justify-content-center g-4">
                    @foreach ($services as $service)
                        <div class="col-xl-4 col-md-6 d-flex services-reveal services-reveal--up">
                            <div class="service-card-v2 w-100">
                                <div class="service-card-v2__icon">
                                    <img src="{{ $service->icon }}" alt="{{ $service->name }}" loading="lazy">
                                </div>

                                <div class="service-card-v2__body">
                                    <h4 class="mb-2">{{ $service->name }}</h4>

                                    <p class="service-card-v2__text mb-0">
                                        {{ $service->short_description }}
                                    </p>

                                    <div class="service-card-v2__meta">
                                        <span>
                                            <i class="fa-solid fa-user-doctor"></i>
                                            {{ $service->service_doctors_count }} {{ __('messages.web.doctors') }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-layer-group"></i>
                                            {{ $service->serviceCategory ? $service->serviceCategory->name : __('messages.common.n/a') }}
                                        </span>
                                        <span>
                                            {{ $service->charges ? getCurrencyIcon() . number_format((float) $service->charges, 2) : __('messages.common.n/a') }}
                                        </span>
                                    </div>

                                    <a href="{{ route('serviceBookAppointment', $service->id) }}"
                                        class="service-card-v2__btn btn btn-primary">
                                        <span>{{ __('messages.web.book_an_appointment') }}</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <!-- end services grid -->

        <!-- start services counter section -->
        <section class="services-counter-section p-t-100 p-b-100">
            <div class="container">
                <div class="services-counter-shell">
                    <div class="row g-0 text-center">
                        <div class="col-xl-3 col-6 services-counter-block">
                            <div class="services-counter-item">
                                <h4>{{ $data['specializationsCount'] }}+</h4>
                                <p>{{ __('messages.specializations') }}</p>
                            </div>
                        </div>
                        <div class="col-xl-3 col-6 services-counter-block">
                            <div class="services-counter-item">
                                <h4>{{ $data['servicesCount'] }}+</h4>
                                <p>{{ __('messages.web.services') }}</p>
                            </div>
                        </div>
                        <div class="col-xl-3 col-6 services-counter-block">
                            <div class="services-counter-item">
                                <h4>{{ $data['doctorsCount'] }}+</h4>
                                <p>{{ __('messages.doctors') }}</p>
                            </div>
                        </div>
                        <div class="col-xl-3 col-6 services-counter-block">
                            <div class="services-counter-item">
                                <h4>{{ $data['patientsCount'] }}+</h4>
                                <p>{{ __('messages.web.satisfied_patient') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end services counter section -->
    </div>
@endsection
