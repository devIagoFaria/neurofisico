@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.web.medical') }}
@endsection
@section('front-content')
    @php
        $styleCss = 'style';
    @endphp
    <div class="home-page">
        <!-- start hero section -->
        <section class="overflow-hidden  position-relative hero-section p-t-100 p-b-100">
            <div class="container p-t-100">
                <div class="hero-bg-shape-1"></div>
                <div class="hero-bg-shape-2"></div>

                <div class="container">
                    <div class="row align-items-center gy-5">
                        <div class="col-lg-6 home-animate home-animate--up">
                            <p class="fw-bold text-uppercase mb-3 text-gradient">
                                {{ $heroSlider['title'] }}
                            </p>

                            <h1 class="hero-title mb-4">
                                {{ $heroSlider['short_description'] }}
                            </h1>

                            <p class="mb-4">
                                {{ __('messages.web.home_page_description') }}
                            </p>

                            <div class="d-flex flex-wrap gap-3">

                                <a href="{{ route('medicalAppointment') }}"
                                    class="btn btn-primary-gradient rounded-pill px-4 py-3">
                                    {{ __('messages.web.book_an_appointment') }} →
                                </a>

                                <a href="{{ route('medicalServices') }}"
                                    class="btn btn-outline-gradient rounded-pill px-4 py-3">
                                    {{ __('messages.web.learn_more') }}
                                </a>

                            </div>

                            <div class="hero-trust mt-5">
                                <div class="hero-trust__item">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>{{ __('messages.web.qualified_doctors') }}</span>
                                </div>
                                <div class="hero-trust__item">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    <span>{{ __('messages.web.easy_booking') }}</span>
                                </div>
                                <div class="hero-trust__item">
                                    <i class="fa-solid fa-shield-heart"></i>
                                    <span>{{ __('messages.web.patient_first_care') }}</span>
                                </div>
                            </div>

                        </div>

                        <!-- Right Content -->
                        <div class="col-lg-6 text-center text-lg-end home-animate home-animate--right">

                            <div class="hero-visual position-relative">
                                <div class="hero-image-shell">
                                    <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('assets/front/images/home/home-page-image.png') }}"
                                        alt="We Provide All Health Care Solution" class="img-fluid hero-image"
                                        loading="lazy">
                                </div>

                                <div class="hero-floating-card hero-floating-card--top">
                                    <div class="hero-floating-card__icon">
                                        <i class="fa-solid fa-stethoscope"></i>
                                    </div>
                                    <div>
                                        <div class="hero-floating-card__title">{{ __('messages.web.247_care') }}</div>
                                        <div class="hero-floating-card__text">{{ __('messages.web.book_an_appointment') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="hero-floating-card hero-floating-card--bottom">
                                    <div class="hero-floating-card__icon">
                                        <i class="fa-solid fa-user-doctor"></i>
                                    </div>
                                    <div>
                                        <div class="hero-floating-card__title">
                                            {{ $doctors->count() > 0 ? $doctors->count() . ' +' : 0 }}
                                        </div>
                                        <div class="hero-floating-card__text">{{ __('messages.web.qualified_doctors') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        {{-- Start Static Section --}}
        <section class="stats-section py-5 shadow-sm">
            <div class="container">
                <div class="row text-center g-4">

                    <!-- Item 1 -->
                    <div class="col-6 col-lg-4 stats-item home-animate home-animate--up home-animate--delay-1">
                        <h2 class="stats-number">
                            {{ is_numeric($aboutExperience->value) && $aboutExperience->value > 0 ? $aboutExperience->value . '+' : 0 }}
                        </h2>
                        <p class="stats-text">{{ __('messages.web.year_experience') }}</p>
                    </div>

                    <!-- Item 2 -->
                    <div class="col-6 col-lg-4 stats-item home-animate home-animate--up home-animate--delay-2">
                        <h2 class="stats-number">{{ $doctors->count() > 0 ? $doctors->count() . '+' : 0 }}</h2>
                        <p class="stats-text">{{ __('messages.web.specialist_doctors') }}</p>
                    </div>

                    <!-- Item 3 -->
                    <div class="col-6 col-lg-4 stats-item home-animate home-animate--up home-animate--delay-3">
                        <h2 class="stats-number">{{ $patients->count() > 0 ? $patients->count() . '+' : 0 }}</h2>
                        <p class="stats-text">{{ __('messages.web.patients_served') }}</p>
                    </div>


                </div>
            </div>
        </section>
        {{-- End Static Section --}}

        <!-- start about section -->
        <section class="about-section p-b-100 p-t-100">
            <div class="container">
                <div class="row align-items-center flex-column-reverse flex-xl-row">
                    <div
                        class="col-xxl-6 col-xl-5 after-rectangle-shape position-relative about-left-content left-shape home-animate home-animate--left">
                        <div class="row position-relative z-index-1">
                            <div class="col-xl-6 col-md-3 about-block">
                                <div class="about-image-box rounded-20 bg-white">
                                    <img src="{{ getSettingValue('about_image_2') }}" alt="About" class="rounded-20"
                                        loading="lazy" />
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-3 about-block">
                                <div class="about-image-box rounded-20 bg-white">
                                    <img src="{{ getSettingValue('about_image_1') }}" alt="About" class="rounded-20"
                                        loading="lazy" />
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-3 about-block">
                                <div
                                    class="about-content-box rounded-20 bg-white d-flex align-items-center justify-content-center h-100">
                                    <div class="text-center">
                                        <h2 class="number-big text-primary">
                                            {{ is_numeric($aboutExperience->value) && $aboutExperience->value > 0 ? $aboutExperience->value . '+' : 0 }}
                                        </h2>
                                        <p class="mb-0">{{ __('messages.web.year_experience') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-3 about-block">
                                <div class="about-image-box bg-white rounded-20">
                                    <img src="{{ getSettingValue('about_image_3') }}" alt="About" class="rounded-20"
                                        loading="lazy" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-xl-7">
                        <div
                            class="about-right-content mb-md-5 mb-4 mb-xl-0 text-center text-xl-start home-animate home-animate--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.about_us') }}</h5>
                            <h2 class="pb-2">{{ getSettingValue('about_title') }}</h2>
                            <p class="paragraph pb-1">
                                {{ getSettingValue('about_short_description') }}
                            </p>
                            <ul class="d-flex ps-0 mb-4 pb-2 justify-content-center justify-content-xl-start flex-wrap">
                                <li class="mb-2">{{ __('messages.web.emergency_help') }}</li>
                                <li class="mb-2">{{ __('messages.web.qualified_doctors') }}</li>
                                <li class="mb-2">{{ __('messages.web.best_professionals') }}</li>
                                <li class="mb-2">{{ __('messages.web.medical_treatment') }}</li>
                            </ul>
                            <a href="{{ route('medicalContact') }}"
                                class="btn btn-primary ">{{ __('messages.web.contact_us') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end about section -->

        <!-- start how-it-work section -->
        <section class="how-work-section p-t-100 p-b-100">
            <div class="container">

                <!-- Heading -->
                <div class="text-center mb-lg-5 mb-4">
                    <h5 class="top-heading mb-3">
                        {{ __('messages.web.working_process') }}
                    </h5>
                    <h2 class="section-title">
                        {{ __('messages.web.how_we_works') }}?
                    </h2>
                </div>

                <div class="row justify-content-center">

                    <!-- Card 1 -->
                    <div class="col-xl-4 col-md-6">
                        <div
                            class="card work-card h-100 text-md-start text-center home-animate home-animate--up home-animate--delay-1">
                            <div class="card-body position-relative">

                                <span class="card-number">01</span>

                                <div class="work-icon">
                                    <i class="fas fa-users"></i>
                                </div>

                                <h4 class="card-title">
                                    {{ __('messages.web.registration') }}
                                </h4>

                                <p class="paragraph mb-0">
                                    {{ __('messages.web.patient_can_do_registration___') }}
                                </p>

                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-xl-4 col-md-6 mt-md-0 mt-4">
                        <div
                            class="card work-card h-100 text-md-start text-center home-animate home-animate--up home-animate--delay-2">
                            <div class="card-body position-relative">

                                <span class="card-number">02</span>

                                <div class="work-icon">
                                    <i class="fas fa-clock"></i>
                                </div>

                                <h4 class="card-title">
                                    {{ __('messages.web.make_appointment') }}
                                </h4>

                                <p class="paragraph mb-0">
                                    {{ __('messages.web.patient_can_book_an_appointment___') }}
                                </p>

                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col-xl-4 col-md-6 mt-xl-0 mt-4 pt-xl-0 pt-lg-3">
                        <div
                            class="card work-card h-100 text-md-start text-center home-animate home-animate--up home-animate--delay-3">
                            <div class="card-body position-relative">

                                <span class="card-number">03</span>

                                <div class="work-icon">
                                    <i class="fas fa-heart"></i>
                                </div>

                                <h4 class="card-title">
                                    {{ __('messages.web.take_treatment') }}
                                </h4>

                                <p class="paragraph mb-0">
                                    {{ __('messages.web.doctors_can_interact___') }}
                                </p>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- end how-it-work section -->

        <!-- start appointment section -->
        <section class="appointmnet-section p-t-100 p-b-100">
            <div class="container">
                <div class="bg-primary border-bmr-100 appointmnet-section__inner-block">
                    <div class="row align-items-center">
                        <div class="col-lg-6 align-self-end d-none d-lg-block home-animate home-animate--left">
                            <img src="{{ asset('assets/front/images/appointment.png') }}" alt="Book An Appointment"
                                class="img-fluid object-image-cover" loading="lazy" />
                        </div>
                        <div class="col-lg-6 contact-form home-animate home-animate--right">
                            <h2 class="pb-2 text-white text-center mb-4 pb-3">{{ __('messages.web.book_an_appointment') }}
                            </h2>
                            <form action="{{ route('front.home.appointment.book') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <input name="first_name" type="text"
                                                class="form-control required form-control-transparent"
                                                placeholder="{{ __('messages.doctor.first_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <input type="text" name="last_name"
                                                class="form-control required form-control-transparent"
                                                placeholder="{{ __('messages.doctor.last_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="contact-form__input-block">
                                            <input type="email" name="email"
                                                class="form-control required form-control-transparent"
                                                placeholder="{{ __('messages.web.email') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block">
                                            <select name="doctor_id" id="frontDoctorId"
                                                class="form-select form-control-transparent required"
                                                data-control="select2" required>

                                                <option value="">
                                                    {{ __('messages.common.select_doctor') }}
                                                </option>

                                                @foreach ($doctors as $id => $doctor)
                                                    <option value="{{ $id }}">
                                                        {{ $doctor }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-form__input-block position-relative">
                                            <input type="text" name="date" id="frontAppointmentDate"
                                                class="form-control form-control-transparent appointment-calendar"
                                                placeholder="{{ __('messages.doctor.select_date') }}" autocomplete="true"
                                                required readonly>
                                            <span
                                                class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4">
                                                <i class="fa-solid fa-calendar-days text-white post"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 text-center mt-4">
                                        <button type="submit"
                                            class="btn btn-light">{{ __('messages.web.appointment_now') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end appointment section -->

        <!-- start services section -->
        <section class="services-section overflow-hidden">
            <div class="container">
                <div class="row align-items-center">

                    <!-- LEFT CONTENT -->
                    <div class="col-xxl-4 home-animate home-animate--left">
                        <div class="text-xxl-start text-center mb-lg-0 mb-5">

                            <h5 class="text-primary top-heading mb-3">
                                {{ __('messages.web.services') }}
                            </h5>

                            <h2 class="fw-bold mb-3">
                                {{ __('messages.web.we_cover_a_big___') }}
                            </h2>

                            <p class="paragraph mb-4">
                                {{ __('messages.web.we_provide_the_special_tips___') }}
                            </p>

                            <a href="{{ route('medicalServices') }}" class="btn btn-primary rounded-pill px-4 py-2">
                                {{ __('messages.web.all_services') }}
                            </a>

                        </div>
                    </div>

                    <!-- RIGHT CAROUSEL -->
                    <div class="col-xxl-8 mt-lg-5 mt-xxl-0 home-animate home-animate--right">
                        <div class="services-carousel-shell">
                            <div class="services-carousel-controls" aria-label="Services carousel controls">
                                <button type="button" class="services-carousel-control services-carousel-control--prev"
                                    aria-label="Previous service">
                                    <i class="fa-solid fa-chevron-left"></i>
                                    <span>{{ __('messages.web.previous') }}</span>
                                </button>

                                <button type="button" class="services-carousel-control services-carousel-control--next"
                                    aria-label="Next service">
                                    <span>{{ __('messages.web.next') }}</span>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>

                            <div class="services-carousel">

                                @foreach ($frontMedicalServices as $service)
                                    <div class="services-slide">
                                        <div class="row g-4 {{ count($service) === 1 ? 'justify-content-center' : '' }}">

                                            @foreach ($service as $item)
                                                <div class="col-12 {{ count($service) === 1 ? 'col-md-9' : 'col-md-6' }} home-animate home-animate--up">
                                                    <div class="service-card text-center h-100 d-flex flex-column">

                                                        <!-- ICON -->
                                                        <div
                                                            class="service-icon mx-auto d-flex align-items-center justify-content-center">
                                                            <img src="{{ $item['icon'] }}" alt="{{ $item['name'] }}"
                                                                loading="lazy">
                                                        </div>

                                                        <!-- TITLE -->
                                                        <h4 class="service-title">
                                                            {{ $item['name'] }}
                                                        </h4>

                                                        <!-- TEXT -->
                                                        <p class="service-text flex-grow-1">
                                                            {{ Str::limit($item['short_description'], 75) }}
                                                        </p>

                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
    <!-- end services section -->

    <!-- start testimonial section -->
    @include('fronts.patient_testimonial')
    <!-- end testimonial section -->
    </div>
@endsection
