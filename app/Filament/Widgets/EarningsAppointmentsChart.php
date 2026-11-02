<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class EarningsAppointmentsChart extends ApexChartWidget
{
    use HasFiltersSchema;

    protected static ?string $chartId = 'earningsAppointmentsChart';
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public string $serviceId = '';
    public ?string $doctorId = '';
    public ?string $serviceCategoryId = '';

    public function getHeading(): string
    {
        return __('messages.admin_dashboard.earnings_from_appointments');
    }

    protected function getOptions(): array
    {
        $appointments = Appointment::with('services')->whereYear('created_at', Carbon::now()->year)
            ->select(DB::raw('MONTH(created_at) as month'), 'appointments.*')->get();

        $transactions = Transaction::with(['user', 'appointment'])
            ->whereStatus('1')
            ->select(DB::raw('MONTH(created_at) as month'), 'transactions.*')
            ->get();

        $months = [
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
        ];

        $monthWiseRecords1 = [];

        $serviceId = $this->filters['serviceId'] ?? null;
        $doctorId = $this->filters['doctorId'] ?? null;
        $serviceCategoryId = $this->filters['serviceCategoryId'] ?? null;

        foreach ($months as $month => $monthName) {
            $monthWiseRecords1[$monthName] = $transactions->where('month', $month)
                ->when($serviceId, function ($query) use ($serviceId) {
                    return $query->where('appointment.service_id', $serviceId);
                })
                ->when($doctorId, function ($query) use ($doctorId) {
                    return $query->where('appointment.doctor_id', $doctorId);
                })
                ->when($serviceCategoryId, function ($query) use ($serviceCategoryId) {
                    return $query->where('appointment.services.category_id', $serviceCategoryId);
                })
                ->sum('amount');
        }

        $data = array_values($monthWiseRecords1);
        $categories = array_keys($monthWiseRecords1);

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
                    'name' => __('messages.user.earning'),
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
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

            'tooltip' => [
                'theme' => 'dark',
                'y' => [
                    'formatter' => 'function (val) {
                return "Amount: $ " + val.toFixed(0);
            }',
                ],
            ],

            'legend' => [
                'show' => false,
            ],
        ];
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('serviceId')
                    ->label(__('messages.common.service'))
                    ->options(Service::pluck('name', 'id')->toArray())
                    ->placeholder(__('messages.common.select_service'))
                    ->searchable()
                    ->default($this->serviceId),
                Select::make('serviceCategoryId')
                    ->label(__('messages.service_categories'))
                    ->options(ServiceCategory::pluck('name', 'id')->toArray())
                    ->placeholder(__('messages.common.select_category'))
                    ->searchable()
                    ->default($this->serviceCategoryId),
                Select::make('doctorId')
                    ->label(__('messages.doctors'))
                    ->options(Doctor::join('users', 'doctors.user_id', '=', 'users.id')->select('doctors.id', DB::raw('CONCAT(users.first_name, " ", users.last_name) as full_name'))->pluck('full_name', 'doctors.id')->toArray())
                    ->placeholder(__('messages.common.select_doctor'))
                    ->searchable()
                    ->default($this->doctorId),
            ]);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    public static function canView(): bool
    {
        return false;
    }
}
