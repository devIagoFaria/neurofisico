<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="icon" href="{{ asset('web/img/hms-saas-favicon.ico') }}" type="image/png">
    <title>{{ __('messages.medicine_bills.medicine_bill') }}</title>
    @if(getCurrencyCode() == 'inr')
        <style>
            body {
                font-family: DejaVu Sans, sans-serif !important;
            }
        </style>
    @endif
    <style>
        * {
            font-family: DejaVu Sans, Arial, "Helvetica", Arial, "Liberation Sans", sans-serif;
        }
    </style>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Lato", sans-serif;
            padding: 30px;
            font-size: 14px;
        }

        .font-color-gray {
            color: #7a7a7a;
        }

        .main-heading {
            font-size: 34px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-right {
            text-align: right;
            vertical-align: top;
            width: 50%;
        }

        .logo, .hospital-name {
            margin-bottom: 8px;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .address {
            margin-top: 60px;
            width: 100%;
        }

        .address tr:first-child td {
            padding-bottom: 10px;
        }

        .items-table {
            width: 100%;
            border: 0;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .items-table thead {
            background: #2f353a;
            color: #fff;
        }

        .items-table td, .items-table th {
            padding: 8px;
            font-size: 14px;
            border-bottom: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }

        .bill-footer {
            margin-top: 15px;
            width: 80%;
            float: right;
            text-align: right;
            margin-right: 8px;
        }

        .number-align {
            text-align: right !important;
        }

        /* bill table */
        .patient-details {
            vertical-align: top;

        }

        .patient-details h3{
            padding: 0px 0px 10px 0px;
        }

        .patient-detail-one td{
            padding: 0px;
        }
        .patient-detail-two td {
            padding: 5px;
        }

        .patient-detail-heading {
            font-size: medium;
        }

        .bill-summary {
            width: 5%;
        }

        .text-right {
            text-align: right !important;
        }

        .bill_info{
            text-align: right;
        }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td class="header-left">
                <div class="main-heading">{{ __('messages.medicine_bills.medicine_bill') }}</div>
            </td>
            <td class="header-right">
                <div class="logo"><img width="100px" src="{{ $data['logo'] }}" alt=""></div>
                <div class="hospital-name">{{ $data['clinic_name'] }}</div>
                <div class="hospital-name font-color-gray">{{ $data['address_one'] }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="address">
                    <tr>
                        <td class="patient-details">
                            <h3>{{ __('messages.patient.details') }} :</h3>
                            <div>
                                <table class="patient-detail-one">
                                    <tr>
                                        <td class="font-weight-bold">{{ __('messages.doctor_appointment.patient') }}:</td>
                                        <td>{{ $medicineBill->patient->user->full_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">{{ __('auth.email') }}:</td>
                                        <td>{{ $medicineBill->patient->user->email }}</td>
                                    </tr>
                                    @if (!empty($medicineBill->patient->user->phone))
                                        <tr>
                                            <td class="font-weight-bold">{{ __('messages.medicine_bills.cell_no') }}:</td>
                                            <td>{{ $medicineBill->patient->user->phone }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="font-weight-bold">{{ __('messages.user.gender') }}:</td>
                                        <td>{{ $medicineBill->patient->user->gender == 0 ? __('messages.staff.male') : __('messages.staff.female') }}</td>
                                    </tr>
                                    @if (!empty($medicineBill->patient->user->dob))
                                        <tr>
                                            <td class="font-weight-bold">{{ __('messages.doctor.dob') }}:</td>
                                            <td>{{ Datetime::createFromFormat('Y-m-d',  $medicineBill->patient->user->dob)->format('jS M, Y g:i A') }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($medicineBill->doctor))
                                        <tr>
                                            <td class="font-weight-bold">{{ __('messages.doctor.doctor') }}:</td>
                                            <td>{{ $medicineBill->doctor->user->full_name }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                        <div class="bill_info">
                            <span class="font-weight-bold patient-detail-heading">{{ __('messages.medicine_bills.bill_id') }}:</span>
                            #{{ $medicineBill->bill_number }}
                            <br>
                            <span class="font-weight-bold patient-detail-heading">{{ __('messages.medicine_bills.bill_date') }}:</span>
                            {{ \Carbon\Carbon::parse($medicineBill->bill_date)->format('jS M,Y g:i A') }}
                        </div>
                    </tr>
                    {{-- <tr> 
                        <td colspan="2">
                            <span class="font-weight-bold patient-detail-heading">{{ __('messages.medicine_bills.bill_id') }}:</span>
                            #{{ $medicineBill->bill_number }}
                            <br>
                            <span class="font-weight-bold patient-detail-heading">{{ __('messages.medicine_bills.bill_date') }}:</span>
                            {{ \Carbon\Carbon::parse($medicineBill->bill_date)->format('jS M,Y g:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"
                            class="font-weight-bold patient-detail-heading">{{ __('messages.patient.details') }}</td>
                    </tr>
                    <tr>
                        <td class="patient-details">
                            <table class="patient-detail-one">
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.doctor_appointment.patient') }}:</td>
                                    <td>{{ $medicineBill->patient->user->full_name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">{{ __('auth.email') }}:</td>
                                    <td>{{ $medicineBill->patient->user->email }}</td>
                                </tr>
                                @if (!empty($medicineBill->patient->user->phone))
                                    <tr>
                                        <td class="font-weight-bold">{{ __('messages.medicine_bills.cell_no') }}:</td>
                                        <td>{{ $medicineBill->patient->user->phone }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.user.gender') }}:</td>
                                    <td>{{ $medicineBill->patient->user->gender == 0 ? __('messages.staff.male') : __('messages.staff.female') }}</td>
                                </tr>
                                @if (!empty($medicineBill->patient->user->dob))
                                    <tr>
                                        <td class="font-weight-bold">{{ __('messages.doctor.dob') }}:</td>
                                        <td>{{ Datetime::createFromFormat('Y-m-d',  $medicineBill->patient->user->dob)->format('jS M, Y g:i A') }}</td>
                                    </tr>
                                @endif
                                @if (!empty($medicineBill->doctor))
                                    <tr>
                                        <td class="font-weight-bold">{{ __('messages.doctor.doctor') }}:</td>
                                        <td>{{ $medicineBill->doctor->user->full_name }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr> --}}
                    <tr>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="items-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.medicine_bills.item_name') }}</th>
                        <th class="number-align">{{ __('messages.medicine.quantity') }}</th>
                        <th class="number-align">{{ __('messages.medicine_bills.price') }}</th>
                        <th class="number-align">{{ __('messages.purchase_medicine.tax') }}</th>
                        <th class="number-align">{{ __('messages.purchase_medicine.amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($medicineBill->saleMedicine) && !empty($medicineBill->saleMedicine))
                        @foreach ($medicineBill->saleMedicine as $index => $saleMedicine)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $saleMedicine->medicine->name }}
                                </td>
                                <td class="number-align">{{ $saleMedicine->sale_quantity }}</td>
                                <td class="number-align">
                                    {{ $saleMedicine->sale_price }}</td>
                                <td class="number-align">
                                    {{ $saleMedicine->tax . '%' }}</td>
                                <td class="number-align">
                                    {{ $saleMedicine->sale_price * $saleMedicine->sale_quantity }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <table class="bill-footer">
                    <tr>
                        <td class="font-weight-bold">{{ __('messages.purchase_medicine.total') . ':' }}</td>
                        <td class="text-gray-900 text-end pe-0">
                            {{ number_format($medicineBill->total, 2) }} </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">{{ __('messages.purchase_medicine.tax') . ':' }}</td>
                        <td class="text-gray-900 text-end pe-0">
                            {{ number_format($medicineBill->tax_amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">{{ __('messages.purchase_medicine.discount') . ':' }}</td>
                        <td class="text-gray-900 text-end pe-0">
                            {{ number_format($medicineBill->discount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">{{ __('messages.purchase_medicine.net_amount') . ':' }}</td>
                        <td class="text-gray-900 text-end pe-0">
                            {{ number_format($medicineBill->net_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
