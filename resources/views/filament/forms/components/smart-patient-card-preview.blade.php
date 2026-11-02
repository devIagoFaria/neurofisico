<div>
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Card Preview -->
        <div class="flex-1 flex justify-center xl:justify-start lg:justify-start md:justify-start 2xl:justify-center"> 
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden"
                style="width: 600px; max-width: 100%;"> <!-- Card Header -->
                @php
                    $state = $getState();

                    $headerColor =
                        is_array($state) && isset($state['header_color']) ? $state['header_color'] : '#3b82f6';
                    $showEmail = is_array($state) && isset($state['show_email']) ? $state['show_email'] : true;
                    $showPhone = is_array($state) && isset($state['show_phone']) ? $state['show_phone'] : true;
                    $showDob = is_array($state) && isset($state['show_dob']) ? $state['show_dob'] : true;
                    $showBloodGroup =
                        is_array($state) && isset($state['show_blood_group']) ? $state['show_blood_group'] : true;
                    $showAddress = is_array($state) && isset($state['show_address']) ? $state['show_address'] : true;
                    $showPatientUniqueId =
                        is_array($state) && isset($state['show_patient_unique_id'])
                            ? $state['show_patient_unique_id']
                            : true;
                @endphp

                <div class="bg-blue-500 p-4" style="background-color: {{ $headerColor }};">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="bg-white dark:bg-gray-200 p-1 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500 dark:text-blue-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h4 class="text-white dark:text-gray-100 text-xl font-bold">Clinic Name</h4>
                        </div>
                        <div class="text-white dark:text-gray-100 text-right">
                            <p>123 Clinic Street</p>
                            <p>City, State 12345</p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Patient Image -->
                        <div class="flex-shrink-0">
                            <div
                                class="bg-gray-200 dark:bg-gray-700 border-2 border-dashed rounded-xl w-24 h-24 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-12 w-12 text-gray-400 dark:text-gray-300" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Patient Info -->
                        <div class="flex-1">
                            <table class="w-full">
                                <tr>
                                    <td class="font-medium pr-2 dark:text-gray-300">{{ __('messages.web.patient_name') . ' :'}}</td>
                                    <td class="dark:text-gray-200">John Doe</td>
                                </tr>
                                @if ($showEmail)
                                    <tr>
                                        <td class="font-medium pr-2 dark:text-gray-300">{{ __('messages.web.email') . ' :'}}</td>
                                        <td class="dark:text-gray-200">john.doe@example.com</td>
                                    </tr>
                                @endif
                                @if ($showPhone)
                                    <tr>
                                        <td class="font-medium pr-2 dark:text-gray-300">{{ __('messages.web.phone') . ' :'}}</td>
                                        <td class="dark:text-gray-200">+1 (555) 123-4567</td>
                                    </tr>
                                @endif
                                @if ($showDob)
                                    <tr>
                                        <td class="font-medium pr-2 dark:text-gray-300">{{ __('messages.patient.dob') . ' :'}}</td>
                                        <td class="dark:text-gray-200">Jan 1, 1990</td>
                                    </tr>
                                @endif
                                @if ($showBloodGroup)
                                    <tr>
                                        <td class="font-medium pr-2 dark:text-gray-300">{{ __('messages.patient.blood_group') . ' :'}}</td>
                                        <td class="dark:text-gray-200">O+</td>
                                    </tr>
                                @endif
                            </table>

                            @if ($showAddress)
                                <div class="mt-2">
                                    <p class="font-medium dark:text-gray-300 inline">{{ __('messages.setting.address') . ' :'}}</p>
                                    <p class="dark:text-gray-200 inline ml-1">123 Main Street, Apt 4B, City, State 12345
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- QR Code and ID -->
                        <div class="flex flex-col items-end">
                            <div
                                class="bg-gray-200 dark:bg-gray-700 border-2 border-dashed rounded w-24 h-24 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-12 w-12 text-gray-400 dark:text-gray-300" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>

                            @if ($showPatientUniqueId)
                                <p class="text-sm font-medium dark:text-gray-300">ID: PAT-001</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
