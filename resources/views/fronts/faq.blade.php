@extends('fronts.layouts.app')
@section('front-title')
    {{ __('messages.faqs') }}
@endsection

@section('front-content')
    <div class="our-faqs-page">
        <!-- start hero section -->
        <section class="faq-hero-section p-t-100 p-b-100">
            <div class="container p-t-30">
                <div class="faq-hero-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 faq-hero-copy faq-reveal faq-reveal--up">
                            <h5 class="text-primary top-heading fs-6 mb-3">
                                {{ __('messages.web.faqs') }}
                            </h5>

                            <h1 class="faq-hero-title mb-3">
                                {{ $heroSlider['title'] }}
                            </h1>

                            <p class="faq-hero-text mb-4">
                                {{ $heroSlider['short_description'] }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 mb-4">
                                <a href="{{ route('medicalAppointment') }}" class="btn btn-primary faq-hero-btn">
                                    {{ __('messages.web.book_an_appointment') }}
                                </a>

                                <a href="{{ route('medicalContact') }}" class="btn btn-outline-primary faq-hero-btn">
                                    {{ __('messages.web.contact_us') }}
                                </a>
                            </div>

                            <div class="faq-hero-points">
                                <div class="faq-point">
                                    <i class="fa-solid fa-circle-question"></i>
                                    <span>{{ __('messages.web.quick_answers') }}</span>
                                </div>
                                <div class="faq-point">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    <span>{{ __('messages.web.booking_help') }}</span>
                                </div>
                                <div class="faq-point">
                                    <i class="fa-solid fa-headset"></i>
                                    <span>{{ __('messages.web.support_ready') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 faq-hero-visual faq-reveal faq-reveal--right">
                            <div class="faq-hero-image-shell">
                                <img src="{{ !empty($heroSlider->slider_image) ? $heroSlider->slider_image : asset('web/media/illustrations/support.png') }}"
                                    alt="{{ __('messages.web.faqs') }}" class="img-fluid faq-hero-image" loading="lazy">

                                <div class="faq-hero-image-badge">
                                    <strong>{{ __('messages.web.faqs') }}</strong>
                                    <span>{{ __('messages.web.helpful_answers_and_support_guidance') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-hero-mini-stats">
                        <div class="faq-mini-stat faq-reveal faq-reveal--up faq-reveal--delay-1">
                            <span class="faq-mini-stat__value">{{ $faqs->count() }}</span>
                            <span class="faq-mini-stat__label">{{ __('messages.web.published_answers') }}</span>
                        </div>
                        <div class="faq-mini-stat faq-reveal faq-reveal--up faq-reveal--delay-2">
                            <span class="faq-mini-stat__value">24/7</span>
                            <span class="faq-mini-stat__label">{{ __('messages.web.support_access') }}</span>
                        </div>
                        <div class="faq-mini-stat faq-reveal faq-reveal--up faq-reveal--delay-3">
                            <span class="faq-mini-stat__value">{{ __('messages.web.easy_booking') }}</span>
                            <span class="faq-mini-stat__label">{{ __('messages.web.booking_flow') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start faq section -->
        <section class="faq-content-section p-b-100">
            <div class="container">
                <div class="faq-content-shell">
                    <div class="faq-content-shell__header text-center mb-5 faq-reveal faq-reveal--up">
                        <h5 class="text-primary top-heading fs-6 mb-3">{{ __('messages.web.faqs') }}</h5>
                        <h2 class="mb-3">{{ __('messages.web.faqs') }}</h2>
                        <p class="paragraph mb-0">
                            {{ __('messages.web.browse_common_questions_below_or_reach_out_if_you_need_a_more_personal_answer') }}
                        </p>
                    </div>

                    @if ($faqs->isNotEmpty())
                        @php
                            $faqSplitIndex = (int) ceil($faqs->count() / 2);
                        @endphp
                        <div class="row g-4">
                            <div class="col-lg-6 faq-reveal faq-reveal--left">
                                <div class="accordion faq-accordion" id="faqAccordionLeft">
                                    @foreach ($faqs->values()->slice(0, $faqSplitIndex) as $index => $faq)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="faqHeadingLeft{{ $index }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#faqCollapseLeft{{ $index }}"
                                                    aria-expanded="false"
                                                    aria-controls="faqCollapseLeft{{ $index }}">
                                                    {{ $faq->question }}
                                                </button>
                                            </h2>
                                            <div id="faqCollapseLeft{{ $index }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="faqHeadingLeft{{ $index }}"
                                                data-bs-parent="#faqAccordionLeft">
                                                <div class="accordion-body">
                                                    <p class="mb-0">{{ $faq->answer }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-lg-6 faq-reveal faq-reveal--right">
                                <div class="accordion faq-accordion" id="faqAccordionRight">
                                    @foreach ($faqs->values()->slice($faqSplitIndex) as $index => $faq)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="faqHeadingRight{{ $index }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#faqCollapseRight{{ $index }}"
                                                    aria-expanded="false"
                                                    aria-controls="faqCollapseRight{{ $index }}">
                                                    {{ $faq->question }}
                                                </button>
                                            </h2>
                                            <div id="faqCollapseRight{{ $index }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="faqHeadingRight{{ $index }}"
                                                data-bs-parent="#faqAccordionRight">
                                                <div class="accordion-body">
                                                    <p class="mb-0">{{ $faq->answer }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="faq-empty-state text-center faq-reveal faq-reveal--up">
                            <h4 class="mb-2">{{ __('messages.web.no_faqs_available') }}</h4>
                            <p class="paragraph mb-0">{{ __('messages.web.please_check_back_soon_for_updated_answers') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        <!-- end faq section -->
    </div>
@endsection
