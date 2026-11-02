<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="{{ getAppName() }}" />
    <link rel="icon" href="{{ getAppFavicon() }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- META ============== -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- OG -->
    <meta name="robots" content="index, follow">

    <!-- Google Fonts -->
    <link rel="preconnect" href="//fonts.googleapis.com">
    <link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
    <link
        href="//fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="{{ asset('js/lang/messages.js') }}"></script>
    <script src="{{ asset('js/lang/lang.js') }}"></script>
    <script>
        alert('Hello World');
    </script>

    @php
        $frontTheme = [
            'primary_start' => getSettingValue('front_primary_start_color') ?? '#14B8A6',
            'primary_end' => getSettingValue('front_primary_end_color') ?? '#2563EB',
            'secondary' => getSettingValue('front_secondary_color') ?? '#0F172A',
            'surface' => getSettingValue('front_surface_color') ?? '#F1FBFA',
            'muted' => getSettingValue('front_muted_color') ?? '#64748B',
            'dark' => getSettingValue('front_dark_color') ?? '#1E293B',
            'success' => getSettingValue('front_success_color') ?? '#16A34A',
            'danger' => getSettingValue('front_danger_color') ?? '#EF4444',
            'info' => getSettingValue('front_info_color') ?? '#6366F1',
            'warning' => getSettingValue('front_warning_color') ?? '#F59E0B',
            'white' => '#FFFFFF',
        ];
    @endphp

    <style>
        :root {
            --front-primary-start: {{ $frontTheme['primary_start'] }};
            --front-primary-end: {{ $frontTheme['primary_end'] }};
            --front-primary-gradient: linear-gradient(117.85deg, {{ $frontTheme['primary_start'] }} 0%, {{ $frontTheme['primary_end'] }} 100%);
            --front-secondary: {{ $frontTheme['secondary'] }};
            --front-surface: {{ $frontTheme['surface'] }};
            --front-muted: {{ $frontTheme['muted'] }};
            --front-dark: {{ $frontTheme['dark'] }};
            --front-success: {{ $frontTheme['success'] }};
            --front-danger: {{ $frontTheme['danger'] }};
            --front-info: {{ $frontTheme['info'] }};
            --front-warning: {{ $frontTheme['warning'] }};
            --front-white: {{ $frontTheme['white'] }};
        }
    </style>

    @vite('resources/css/front/third-party.css')
    @vite('resources/js/front/third-party.js')

    @vite('resources/css/front/scss/main.scss')
    @vite('resources/js/front/front.js')

    {{-- <link href="{{ mix('css/front-third-party.css') }}" rel="stylesheet" type="text/css"> --}}
    {{-- <link href="{{ mix('css/front-pages.css') }}" rel="stylesheet" type="text/css"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-datepicker/bootstrap-datepicker.css') }}"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/intlTelInput.css') }}"> --}}

    <!-- Document Title ===================== -->
    <title>@yield('front-title') | {{ getAppName() }}</title>
    {{-- <script src="{{ asset('assets/front/vendor/bootstrap.bundle.min.js') }}"></script> --}}
    {{-- <script src="{{ mix('js/front-third-party.js') }}"></script> --}}
    {{-- <script src="{{ mix('js/front-pages.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script> --}}
    <!-- JavaScript Bundle with Popper -->
    <script data-turbo-eval="false">
        let currencyIcon = '{{ getCurrencyIcon() }}'
        let isSetFirstFocus = false
        let csrfToken = "{{ csrf_token() }}"
        let defaultCountryCodeValue = "{{ getSettingValue('default_country_code') }}"
    </script>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    @routes

    <script data-turbo-eval="false">
        let appointmentStripePaymentUrl = '{{ url('appointment-stripe-charge') }}';
        let stripe = '';
        @if (getSettingValue('stripe_enable') && getSettingValue('stripe_key'))
            stripe = Stripe('{{ getSettingValue('stripe_key') }}');
        @endif
        let manually = "{{ \App\Models\Appointment::MANUALLY }}";
        let paystack = "{{ \App\Models\Appointment::PAYSTACK }}";
        let paypal = "{{ \App\Models\Appointment::PAYPAL }}";
        let stripeMethod = "{{ \App\Models\Appointment::STRIPE }}";
        let razorpayMethod = "{{ \App\Models\Appointment::RAZORPAY }}";
        let authorizeMethod = "{{ \App\Models\Appointment::AUTHORIZE }}";
        let paytmMethod = "{{ \App\Models\Appointment::PAYTM }}";
        let checkLanguageSession = '{{ checkLanguageSession() }}';
        let options = {
            'key': "{{ config('payments.razorpay.key') }}",
            'amount': 0, //  100 refers to 1
            'currency': 'INR',
            'name': "{{ getAppName() }}",
            'order_id': '',
            'description': '',
            'image': '{{ getAppLogo() }}', // logo here
            'callback_url': "{{ route('razorpay.success') }}",
            'prefill': {
                'email': '', // recipient email here
                'name': '', // recipient name here
                'contact': '', // recipient phone here
                'appointmentID': '', // appointmentID here
            },
            'readonly': {
                'name': 'true',
                'email': 'true',
                'contact': 'true',
            },
            'theme': {
                'color': '{{ $frontTheme['primary_end'] }}',
            },
            'modal': {
                'ondismiss': function() {
                    $('.book-appointment-message').css('display', 'block');
                    let response =
                        '<div class="gen alert alert-danger">Appointment created successfully and payment not completed.</div>';
                    $('.book-appointment-message').html(response).delay(5000).hide('slow');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
            },
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Lang !== 'undefined') {
                Lang.setLocale('{{ checkLanguageSession() }}');
            }
        });
    </script>
</head>

<body>
    @include('fronts.layouts.header')
    <div class="container mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="z-index: 9999; position: relative;">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
    @yield('front-content')
    @include('fronts.layouts.footer')
</body>

</html>
