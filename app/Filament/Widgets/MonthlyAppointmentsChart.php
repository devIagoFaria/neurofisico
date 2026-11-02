<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyAppointmentsChart extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyAppointmentsChart';
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('messages.monthly_appointments');
    }

    protected function getOptions(): array
    {
        $doctor = Auth::user()->doctor;

        $currentYear = Carbon::now()->year;

        $monthlyCounts = [];
        $months = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()
            ->month($i)
            ->translatedFormat('M');
            $months[] = $monthName;

            if ($doctor) {
                $count = Appointment::where('doctor_id', $doctor->id)
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $i)
                    ->count();
                $monthlyCounts[] = $count;
            } else {
                $monthlyCounts[] = 0;
            }
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 320,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
                'parentHeightOffset' => 0,
            ],
            'series' => [
                [
                    'name' => __('messages.appointments'),
                    'data' => $monthlyCounts,
                ],
            ],
            'xaxis' => [
                'categories' => $months,
                'axisTicks' => ['show' => false],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'grid' => [
                'show' => false,
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 1,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'shadeIntensity' => 0.4,

                    'opacityFrom' => 0.65,
                    'opacityTo' => 0.00,

                    'stops' => [20, 100, 100],
                ],
            ],

            'colors' => ['#3B82F6'],

            'markers' => [
                'size' => 0,
                'hover' => ['size' => 6],
            ],

            'dataLabels' => [
                'enabled' => false,
            ],

            'legend' => [
                'show' => false,
            ],
        ];
    }
}
