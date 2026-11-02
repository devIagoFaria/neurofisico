<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- 2. Appointment Stats -->
        <div class="lg:col-span-12 order-2">
            @if (getLogInUser()->hasRole('doctor'))
                @livewire(\App\Filament\Doctor\Widgets\DoctorStateOverview::class)
            @elseif (getLogInUser()->hasRole('patient'))
                @livewire(\App\Filament\Patient\Widgets\PatientStateOverview::class)
            @else
                @livewire(\App\Filament\Widgets\StateOverview::class)
            @endif
        </div>

        <!-- 3. Monthly Earning Chart -->
        <div class="{{ getLogInUser()->hasRole('patient') ? 'lg:col-span-12' : 'lg:col-span-12' }} order-3">
            @livewire(\App\Filament\Doctor\Widgets\MonthlyAppointmentsChart::class)
        </div>

        <!-- 4. Chart Widgets -->
        <div class="{{ getLogInUser()->hasRole('patient') ? 'lg:col-span-12' : 'lg:col-span-6' }} order-4">
            <div>
                @if (getLogInUser()->hasRole('doctor'))
                    {{-- @livewire(\App\Filament\Doctor\Widgets\MonthlyAppointmentsChart::class) --}}
                @elseif (getLogInUser()->hasRole('patient'))
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                        <div class="dashboard-equal-height-tables">
                            @livewire(\App\Filament\Patient\Widgets\TodayAppointmentsTable::class)
                        </div>
                        <div class="dashboard-equal-height-tables">
                            @livewire(\App\Filament\Patient\Widgets\UpcomingAppointmentsTable::class)
                        </div>
                    </div>
                @else
                    @livewire(\App\Filament\Widgets\ThisMonthAppointmentsChart::class)
                @endif
            </div>
        </div>

        @if (!getLogInUser()->hasRole('patient'))
            <div class="lg:col-span-12 order-5">
                <div class="grid grid-cols-1 {{ getLogInUser()->hasRole('doctor') ? 'lg:grid-cols-1' : 'lg:grid-cols-2' }} gap-6 items-stretch">

                    <!-- Recent Patients / Doctor Appointments -->
                    <div class="dashboard-equal-height-tables">
                        @if (getLogInUser()->hasRole('doctor'))
                            @livewire(\App\Filament\Doctor\Widgets\RecentAppointmentsTable::class)
                        @else
                            @livewire(\App\Filament\Widgets\RecentPatientsRegistrationTable::class)
                        @endif
                    </div>

                    <!-- Today Appointments -->
                    @if (!getLogInUser()->hasRole('doctor'))
                        <div class="dashboard-equal-height-tables">
                            @livewire(\App\Filament\Widgets\TodayAppointmentsTable::class)
                        </div>
                    @endif

                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
