<header class="position-relative header">
    <div class="container d-flex align-items-center justify-content-between ">
        <a href="{{ route('medical') }}" class="header-logo">
            <img src="{{ getAppLogo() }}" alt="InfyCare" class="object-cover front-app-logo" loading="lazy" />
        </a>
        <nav class="navbar navbar-expand-lg navbar-light justify-content-end py-0">
            <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center py-2 py-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('medical') ? 'active' : '' }}"
                            href="{{ route('medical') }}">{{ __('messages.web.home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('medical-doctors*') ? 'active' : '' }}"
                            href="{{ route('medicalDoctors') }}">{{ __('messages.web.our_team') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('medical-services*') ? 'active' : '' }}"
                            href="{{ route('medicalServices') }}">{{ __('messages.web.services') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('medical-about-us*') ? 'active' : '' }}"
                            href="{{ route('medicalAboutUs') }}">{{ __('messages.web.about_us') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('medical-contact*') ? 'active' : '' }}"
                            href="{{ route('medicalContact') }}">{{ __('messages.web.contact_us') }}</a>
                    </li>

                    {{-- Language switcher --}}
                </ul>

                <div class="text-lg-end header-btn-grp ms-xxl-5 ms-lg-3">
                    @if (getLogInUser())
                        <a href="{{ getDashboardURL() }}" class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0">
                            {{ __('messages.dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('filament.auth.auth.login') }}"
                            class="btn btn-outline-primary me-xxl-3 me-2 mb-3 mb-lg-0">
                            {{ __('messages.login') }}
                        </a>
                    @endif

                    <a href="{{ route('medicalAppointment') }}" class="btn btn-primary mb-3 mb-lg-0">
                        {{ __('messages.web.book_an_appointment') }}
                    </a>
                </div>
            </div>
        </nav>
    </div>
</header>
