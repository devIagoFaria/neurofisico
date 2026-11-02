<!DOCTYPE html>
<html lang="en">

<head>
    <base href="../../">
    <title>{{ getAppName() }}</title>
    <link rel="icon" href="{{ getAppFavicon() }}" type="image/png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="flex flex-col min-h-screen">
        <div class="flex flex-col lg:flex-row flex-1">

            <div class="flex flex-col flex-1 py-10">
                <div class="flex justify-center items-center flex-1">
                    <div class="w-full max-w-2xl bg-white shadow-lg rounded-xl p-6 lg:p-10">

                        <form id="frmContact" method="post"
                            action="{{ route('authorize.onboard', ['appointmentId' => $appointment->id]) }}"
                            class="space-y-6">
                            @csrf

                            <!-- Header -->
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Billing Details</h2>
                                <p class="text-gray-500 text-sm">
                                    Payment for booking appointment with doctor :
                                    {{ $doctorName->user->full_name }} at
                                    {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}
                                    {{ $appointment->from_time }} {{ $appointment->from_time_type }}
                                    to {{ $appointment->to_time }} {{ $appointment->to_time_type }}
                                </p>
                            </div>

                            <!-- Alerts -->
                            @if (session('success_msg'))
                                <div class="bg-green-100 text-green-700 p-3 rounded">
                                    {{ session('success_msg') }}
                                </div>
                            @endif

                            @if (session('error_msg'))
                                <div class="bg-red-100 text-red-700 p-3 rounded">
                                    {{ session('error_msg') }}
                                </div>
                            @endif

                            <div id="errorCard" class="hidden bg-red-100 text-red-700 p-3 rounded">
                                <ul id="errorMessage" class="list-disc ml-5">
                                    <li class="error"></li>
                                </ul>
                            </div>

                            <!-- Name on Card -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Name On Card:<span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="cardHolderName" name="owner" value="{{ old('owner') }}"
                                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300"
                                    placeholder="{{ __('messages.name_on_card') }}" required>
                            </div>

                            <!-- Card Number -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Card Number:<span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="cardNumber" name="cardNumber" maxlength="19"
                                        class="w-full border rounded-lg px-3 py-2 pr-24 focus:outline-none focus:ring focus:ring-blue-300"
                                        placeholder="{{ __('messages.card_number') }}" required>

                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-2">
                                        <img src="{{ asset('assets/front/images/payment_images/visa.svg') }}"
                                            class="h-5">
                                        <img src="{{ asset('assets/front/images/payment_images/mastercard.svg') }}"
                                            class="h-5">
                                        <img src="{{ asset('web/media/svg/payment-methods/americanexpress.svg') }}"
                                            class="h-5">
                                    </div>
                                </div>
                            </div>

                            <!-- Expiry + CVV -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <!-- Month -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Expiry Month:<span class="text-red-500">*</span>
                                    </label>
                                    <select name="expiration-month" id="expiryMonth"
                                        class="w-full border rounded-lg px-3 py-2" required>
                                        <option value="">Select Month</option>
                                        @foreach ($months as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Year -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Expiry Year:<span class="text-red-500">*</span>
                                    </label>
                                    <select name="expiration-year" id="expiryYear"
                                        class="w-full border rounded-lg px-3 py-2" required>
                                        <option value="">Select Year</option>
                                        @for ($i = date('Y'); $i <= date('Y') + 15; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <!-- CVV -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        CVV:<span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="cvv" id="cvv" minlength="3" maxlength="4"
                                        class="w-full border rounded-lg px-3 py-2" required
                                        placeholder="{{ __('messages.cvv') }}">
                                </div>

                            </div>
                            <!-- Amount -->
                            <div class="text-gray-700 font-medium">
                                Payment: {{ $appointment->payable_amount }} {{ getCurrencyCode() }}
                            </div>

                            <!-- Buttons -->
                            <div class="flex justify-between items-center pt-4">
                                <a href="{{ route('authorize.failed') }}">
                                    <button type="button"
                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                                        Cancel
                                    </button>
                                </a>

                                <button type="submit" id="authoriseSubmitBtn"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                    Submit
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('web/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/helper.js') }}"></script>
    <script>
        $('#cardNumber').on('input', function() {
            let value = $(this).val().replace(/\D/g, ''); // remove non-digits

            // limit to 16 digits
            value = value.substring(0, 16);

            // add space after every 4 digits
            value = value.replace(/(.{4})/g, '$1 ').trim();

            $(this).val(value);
        });
    </script>
</body>

</html>
