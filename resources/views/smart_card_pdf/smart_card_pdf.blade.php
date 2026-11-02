@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ getAppName() }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .card-wrapper {
            width: 600px;
            border: 1px solid #ccc;
            border-radius: 16px;
            overflow: hidden;
            margin: 0 auto;
        }

        .card-header {
            padding: 15px 20px;
            color: #fff;
        }

        .header-table {
            display: table;
            width: 100%;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left img {
            height: 45px;
            margin-right: 10px;
        }

        .clinic_name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .clinic_address {
            font-size: 11px;
            text-align: right;
            line-height: 1.3;
        }

        .card-body {
            padding: 20px;
        }

        .body-table {
            display: table;
            width: 100%;
        }

        .left-section {
            display: table-cell;
            vertical-align: top;
            width: 70%;
        }

        .right-section {
            display: table-cell;
            vertical-align: top;
            width: 30%;
            text-align: center;
        }

        .photo-row {
            display: table;
            width: 100%;
        }

        .photo-cell {
            display: table-cell;
            width: 120px;
        }

        .photo-cell img {
            width: 110px;
            height: 110px;
            border-radius: 8px;
            border: 1px solid #ccc;
            object-fit: cover;
        }

        .details-cell {
            display: table-cell;
            padding-left: 12px;
            vertical-align: top;
        }

        table.details-table td {
            padding: 3px 4px;
            font-size: 12px;
        }

        table.details-table td:first-child {
            font-weight: bold;
            width: 120px;
        }

        .address-text {
            margin-top: 10px;
            font-size: 12px;
        }

        .qr-section img {
            width: 110px;
            height: 110px;
        }

        .patient_unique_id {
            font-size: 13px;
            margin-top: 5px;
        }

        .logo-img {
            height: 45px;
            vertical-align: middle;
        }

        .header-left-inner {
            display: table;
        }

        .header-left-inner img {
            display: table-cell;
            vertical-align: middle;
        }

        .clinic_name_wrapper {
            display: table-cell;
            vertical-align: middle;
            padding-left: 8px;
        }

        .clinic_name {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.2;
        }

        .qr-wrapper {
            text-align: center;
            margin-bottom: 5px;
        }

        .id-block {
            text-align: center;
            margin-top: 5px;
        }

        .patient_unique_id_span {
            font weight: bold;
            font-size: 12px;
        }

        .patient_unique_id {
            font-size: 14px;
            font-weight: bold;
        }

        .header-left-inner .clinic_name {
            margin: 0;
            padding: 0;
        }
    </style>


</head>

<body>
    <div class="card-wrapper">
        @php
            $logoBase64 = getPdfImageBase64($logo, public_path('neurofisico-logo.png'));
            $patientPhotoBase64 = getPdfImageBase64(
                !empty($data->user->profile_image) ? $data->user->profile_image : null,
                public_path('web/media/avatars/male.png')
            );
        @endphp

        <div class="card-header" style="background-color: {{ $data->smartPatientCard->header_color }};">
            <div class="header-table">
                <div class="header-left">
                    <div class="header-left-inner">
                        @if ($logoBase64)
                            <img class="logo-img" src="data:image/png;base64,{{ $logoBase64 }}">
                        @endif
                        <div class="clinic_name_wrapper">
                            <div class="clinic_name">{{ $clinic_name }}</div>
                        </div>
                    </div>
                </div>

                <div class="header-right clinic_address">
                    {{ $address_one }}
                </div>
            </div>

        </div>

        <div class="card-body">
            <div class="body-table">

                <!-- LEFT CONTENT -->
                <div class="left-section">
                    <div class="photo-row">
                        <div class="photo-cell">
                            @if ($patientPhotoBase64)
                                <img src="data:image/png;base64,{{ $patientPhotoBase64 }}">
                            @endif
                        </div>

                        <div class="details-cell">
                            <table class="details-table">
                                <tr>
                                    <td>{{ __('messages.web.name') }}:</td>
                                    <td>{{ $data->user->full_name ?? '' }}</td>
                                </tr>
                                @if ($data->smartPatientCard->show_email)
                                    <tr>
                                        <td>{{ __('messages.web.email') }}:</td>
                                        <td>{{ $data->user->email ?? '' }}</td>
                                    </tr>
                                @endif
                                @if ($data->smartPatientCard->show_phone)
                                    <tr>
                                        <td>{{ __('messages.web.contact') }}:</td>
                                        <td>{{ $data->user->contact ?? '' }}</td>
                                    </tr>
                                @endif
                                @if ($data->smartPatientCard->show_dob)
                                    <tr>
                                        <td>{{ __('messages.patient.dob') }}:</td>
                                        <td>{{ $data->user->dob ?? '' }}</td>
                                    </tr>
                                @endif
                                @if ($data->smartPatientCard->show_blood_group)
                                    <tr>
                                        <td>{{ __('messages.patient.blood_group') }}:</td>
                                        <td>{{ \App\Models\Patient::BLOOD_GROUP_ARRAY[$data?->user?->blood_group] ?? '' }}
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if ($data->smartPatientCard->show_address)
                        <div class="address-text">
                            <strong>{{ __('messages.setting.address') }}:</strong>
                            {{ $data->address->address1 ?? '' }} {{ $data->address->address2 ?? '' }}
                        </div>
                    @endif
                </div>

                <!-- QR SECTION -->
                <div class="right-section qr-section">
                    <div class="qr-wrapper">
                        <img
                            src="data:image/png;base64,{{ base64_encode(QrCode::size(130)->generate(route('patient_show', $data->patient_unique_id))) }}">
                    </div>

                    <div class="id-block">
                        <div class="patient_unique_id">{{ __('messages.id') }}: {{ $data->patient_unique_id ?? '' }}</div>
                    </div>
                </div>


            </div>
        </div>

    </div>


</body>

</html>
