<x-filament::modal width="2xl">

@php
    $record = auth()->user()->patient;
@endphp
@if (!empty($record->smartPatientCard))
    <x-slot name="trigger">
        <button type="button" class="fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-300 fi-btn fi-outlined fi-size-md" title="Smart Patient Card">
            <x-filament::icon icon="heroicon-o-credit-card" class="h-5 w-5" />
        </button>
    </x-slot>
@endif

    <style>
        /* ALL YOUR CSS SAME */
        .card-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        @media (min-width: 768px) {
            .card-container {
                flex-direction: row;
            }
        }
        .card-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .patient-card {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 600px;
            max-width: 100%;
        }
        .card-header { padding: 16px; }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-box {
            padding: 4px;
            border-radius: 4px;
        }
        .app-name {
            color: #fff;
            font-size: 20px;
            font-weight: bold;
        }
        .header-right {
            color: #fff;
            text-align: right;
        }
        .card-body { padding: 16px; }
        .body-content {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        @media (min-width: 768px) {
            .body-content {
                flex-direction: row;
            }
        }
        .patient-image-box {
            flex-shrink: 0;
            background-color: #e5e7eb;
            border-radius: 12px;
            width: 96px;
            height: 96px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .patient-image-box img {
            border-radius: 4px;
            width: 110px;
            height: auto;
        }
        .patient-info { flex: 1; }
        .patient-info table { width: 100%; }
        .patient-info td { padding: 2px 0; }
        .patient-info .label {
            font-weight: 500;
            padding-right: 8px;
            color: #4b5563;
        }
        .patient-info .value { color: #374151; }
        .address-block { margin-top: 8px; }
        .address-block .label {
            font-weight: 500;
            color: #4b5563;
        }
        .address-block .value {
            color: #374151;
            margin-left: 4px;
        }
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .qr-section p {
            font-size: 14px;
            font-weight: 500;
            color: #4b5563;
            margin-top: 4px;
        }
    </style>
@if (!empty($record->smartPatientCard))
    <div>
        <div class="card-container">
            <div class="card-wrapper">
                <div class="patient-card">

                    @php
                        $headerColor = $record->smartPatientCard->header_color;

                        $showEmail = $record->smartPatientCard->show_email;
                        $showPhone = $record->smartPatientCard->show_phone;
                        $showDob = $record->smartPatientCard->show_dob;
                        $showBloodGroup = $record->smartPatientCard->show_blood_group;
                        $showAddress = $record->smartPatientCard->show_address;
                        $showPatientUniqueId = $record->smartPatientCard->show_patient_unique_id;

                        $address = \App\Models\Setting::where('key', 'address_one')->value('value');
                    @endphp

                    <div class="card-header" style="background-color: {{ $headerColor }};">
                        <div class="header-content">
                            <div class="header-left">
                                <div class="logo-box">
                                    <img src="{{ getAppLogo() }}" width="40">
                                </div>
                                <h4 class="app-name">{{ getAppName() }}</h4>
                            </div>
                            <p class="header-right">
                                {{ $address ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="body-content">

                            <div class="patient-image-box">
                                <img src="{{ !empty($record->user->profile_image) ? $record->user->profile_image : asset('web/media/avatars/male.png') }}">
                            </div>

                            <div class="patient-info">
                                <table>
                                    <tr>
                                        <td class="label">{{ __('messages.web.name') }}:</td>
                                        <td class="value">{{ $record->user->full_name ?? '-' }}</td>
                                    </tr>

                                    @if ($showEmail)
                                    <tr>
                                        <td class="label">{{ __('messages.web.email') }}:</td>
                                        <td class="value">{{ $record->user->email ?? '-' }}</td>
                                    </tr>
                                    @endif

                                    @if ($showPhone)
                                    <tr>
                                        <td class="label">{{ __('messages.web.contact') }}:</td>
                                        <td class="value">{{ $record->user->contact ?? '-' }}</td>
                                    </tr>
                                    @endif

                                    @if ($showDob)
                                    <tr>
                                        <td class="label">{{ __('messages.patient.dob') }}:</td>
                                        <td class="value">{{ \Carbon\Carbon::parse($record->user->dob)->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                    @endif

                                    @if ($showBloodGroup)
                                    <tr>
                                        <td class="label">{{ __('messages.patient.blood_group') }}:</td>
                                        <td class="value">{{ \App\Models\Patient::BLOOD_GROUP_ARRAY[$record?->user?->blood_group] ?? '-' }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <div class="qr-section">
                                {!! QrCode::size(90)->generate(route('patient_show', $record->patient_unique_id)) !!}

                                @if ($showPatientUniqueId)
                                    <p>{{ __('messages.id') }}: {{ $record->patient_unique_id }}</p>
                                @endif

                                <a href="{{ route('admin.smartCardPdf', $record->id) }}" target="_blank"><x-heroicon-o-arrow-down-tray class="w-6 h-6 text-gray-600 hover:text-black fw-bolder" /></a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endif

</x-filament::modal>
