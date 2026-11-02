<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ThisMonthAppointmentsChart extends ApexChartWidget
{
    use HasFiltersSchema;

    protected static ?string $chartId = 'thisMonthAppointmentsChart';

    public ?string $month = '';
    public ?string $year = '';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        $user = auth()->user();
        if ($user->hasRole('patient')) {
            return __('messages.monthly_expense');
        }
        return __('Consultas deste mês');
    }

    protected function getOptions(): array
    {
        $user = auth()->user();
        $data = [];
        $labels = [];

        $month = $this->filters['month'] ?? Carbon::now()->month;
        $year = $this->filters['year'] ?? Carbon::now()->year;

        $dateObj = Carbon::create($year, $month, 1);
        $daysInMonth = $dateObj->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i)->format('Y-m-d');
            $labels[] = $i . ' ' . $dateObj->format('M');

            if ($user->hasRole('doctor')) {
                $loggedInDoctor = Doctor::whereUserId($user->id)->first();
                if ($loggedInDoctor) {
                    $doctorAppointmentIds = Appointment::where('doctor_id', $loggedInDoctor->id)
                        ->pluck('appointment_unique_id');
                    $amount = Transaction::whereIn('appointment_id', $doctorAppointmentIds)
                        ->whereDate('created_at', $date)
                        ->whereStatus(Transaction::SUCCESS)
                        ->sum('amount');
                } else {
                    $amount = 0;
                }
            } elseif ($user->hasRole('patient')) {
                $loggedInPatient = $user->patient;
                if ($loggedInPatient) {
                    $amount = Transaction::join('appointments', 'transactions.appointment_id', '=', 'appointments.appointment_unique_id')
                        ->where('appointments.patient_id', $loggedInPatient->id)
                        ->whereDate('transactions.created_at', $date)
                        ->where('transactions.status', Transaction::SUCCESS)
                        ->sum('transactions.amount');
                } else {
                    $amount = 0;
                }
            } else { // Clinic Admin / Staff
                $amount = Transaction::whereDate('created_at', $date)
                    ->whereStatus(Transaction::SUCCESS)
                    ->sum('amount');
            }
            $data[] = (float) $amount;
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
            ],
            'series' => [
                [
                    'name' => 'Amount',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'axisTicks' => ['show' => false],
                'labels' => [
                    'style' => [
                        'colors' => '#6B7280',
                        'fontSize' => '12px',
                    ],
                ],
            ],
            'grid' => [
                'show' => false,
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'shadeIntensity' => 0.4,
                    'opacityFrom' => 0.65,
                    'opacityTo' => 0.05,
                    'stops' => [20, 100, 100],
                ],
            ],
            'colors' => ['#10B981'], // Green
            'markers' => [
                'size' => 0,
                'hover' => ['size' => 6],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'theme' => 'dark',
            ],
            'legend' => [
                'show' => false,
            ],
        ];
    }

    public function filtersSchema(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Select::make('month')
                    ->label(__('messages.admin_dashboard.month'))
                    ->options([
                        1 => __('messages.months.jan'),
                        2 => __('messages.months.feb'),
                        3 => __('messages.months.mar'),
                        4 => __('messages.months.apr'),
                        5 => __('messages.months.may'),
                        6 => __('messages.months.jun'),
                        7 => __('messages.months.jul'),
                        8 => __('messages.months.aug'),
                        9 => __('messages.months.sep'),
                        10 => __('messages.months.oct'),
                        11 => __('messages.months.nov'),
                        12 => __('messages.months.dec'),
                    ])
                    ->native(false)
                    ->extraAttributes(['class' => 'no-clear'])
                    ->default(Carbon::now()->month),
                Select::make('year')
                    ->label(__('messages.doctor.year'))
                    ->options(array_combine(range(date('Y'), date('Y') - 50), range(date('Y'), date('Y') - 50)))
                    ->native(false)
                    ->extraAttributes(['class' => 'no-clear'])
                    ->default(Carbon::now()->year),
            ]);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }
}
