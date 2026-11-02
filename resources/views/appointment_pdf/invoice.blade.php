<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ getAppName() }}</title>

    <style>
        @font-face {
            font-family: 'Poppins';
            src: url('{{ public_path('theme/fonts/Poppins-Regular.ttf') }}') format("truetype");
            font-weight: normal;
        }

        body {
            font-family: Poppins, DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 22px;
            margin: 0;
        }

        h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .label {
            font-weight: bold;
            width: 35%;
        }

        .value {
            color: #5E5E5E;
        }

        .divider {
            border-bottom: 1px solid #ccc;
            margin: 15px 0;
        }

        .appointment-table {
            margin: 15px 0;
        }

        .box {
            background: #F4F4F4;
            width: 70%;
            padding: 5px;
        }

        .amount-table td,
        .amount-table th {
            padding: 6px 8px;
        }

        .payable-amount {
            background: #F4F4F4;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 10px;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>

<body>
    @php
        $logoBase64 = getPdfImageBase64($logo, public_path('neurofisico-logo.png'));
    @endphp

    <!-- HEADER -->
    <table class="table w-100 mb-5">
        <tbody>
            <tr>
                <td class="w-50 text-center">
                    @if ($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}"
                            style="width:70px;margin-left:70%;margin-bottom:40px;">
                    @endif
                </td>
                <h1 style="margin-right:100%;margin-top:10px;">InfyCare</h1>
            </tr>
        </tbody>
    </table>

    <!-- DOCTOR & PATIENT -->
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td class="label">{{ __('messages.doctor_session.doctor') . ' ' . __('messages.web.name') . ':'}}</td>
                        <td class="value">{{ $datas->doctor->user->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('messages.doctor_session.doctor') . ' ' . __('messages.web.email') . ':'}}</td>
                        <td class="value">{{ $datas->doctor->user->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('messages.common.service') . ':'}}</td>
                        <td class="value">{{ $datas->services->name }}</td>
                    </tr>
                </table>
            </td>

            <td width="50%">
                <table>
                    <tr>
                        <td class="label">{{  __('messages.web.patient_name') . ':'}}</td>
                        <td class="value">{{ $datas->patient->user->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('messages.appointment.patient') . ' ' . __('messages.web.email') . ':'}}</td>
                        <td class="value">{{ $datas->patient->user->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('messages.appointment.payment_method') . ':'}}</td>
                        <td class="value">
                            {{
                                __('messages.payment_method.' . strtolower(
                                    \Illuminate\Support\Str::snake(
                                        \App\Models\Appointment::PAYMENT_METHOD[$datas->payment_method]
                                    )
                                ))
                            }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('messages.appointment.payment_status') . ':'}}</td>
                        <td class="value">
                            {{
                                __('messages.transaction.' . strtolower(
                                    \Illuminate\Support\Str::snake(
                                    \App\Models\Appointment::PAYMENT_TYPE[$datas->payment_type]
                                    )
                                ))
                            }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- APPOINTMENT -->
    <table class="appointment-table">
        <tr>
            <td width="50%">
                <strong>{{ __('messages.pdf.appointment_date')  . ':' }}</strong><br>
                <span class="value">
                    {{ \Carbon\Carbon::parse($datas->date)->format('M d, Y') }}
                </span>
            </td>
            <td width="50%">
                <strong>{{ __('messages.pdf.appointment_time')  . ':' }}</strong><br>
                <span class="value">
                    {{ $datas->from_time }} {{ $datas->from_time_type }}
                    to
                    {{ $datas->to_time }} {{ $datas->to_time_type }}
                </span>
            </td>
        </tr>
    </table>

    <!-- AMOUNT -->
    <table class="appointment-table">
        <tr>
            <td width="50%">
                <div class="box">
                    <strong>{{ __('messages.pdf.invoice_amount') . ':'}}</strong><br>
                    {{ getAmountToWord($datas->payable_amount) }}
                </div>
            </td>

            <td width="50%">
                <table class="amount-table">
                    <tr>
                        <td><strong>{{ __('messages.purchase_medicine.amount') . ':'}}</strong></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.appointment.charge') . ':'}}</td>
                        <td class="text-right">
                            {{ getCurrencyIcon() }}{{ $datas->services->charges }}
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.pdf.extra_charge') . ':'}}</td>
                        <td class="text-right">
                            {{ getCurrencyIcon() }}{{ $datas->payable_amount - $datas->services->charges }}
                        </td>
                    </tr>
                    <tr class="payable-amount">
                        <td>{{ __('messages.appointment.payable_amount') . ':'}}</td>
                        <td class="text-right">
                            {{ getCurrencyFormat(getCurrencyCode(), $datas->payable_amount) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- DESCRIPTION -->
    @if ($datas->description)
        <h3>{{ __('messages.appointment.description') . ':'}}</h3>
        <p class="value">{!! nl2br(e($datas->description)) !!}</p>
    @endif

</body>

</html>
