<?php

namespace App\Filament\Resources\LivestockResource\Widgets;

use App\Models\OrderOfPayment;
use App\Services\DashboardService;
use Filament\Widgets\ChartWidget;

class TotalCollectionYearly extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    public ?string $filter = '2025';
    protected static ?string $maxHeight = '300px'; 

    protected function getData(): array
    {   
        $dashboardService = new DashboardService();
        $yearFilter = $this->filter;
        $data = $dashboardService->getYearlyTotalCollection($yearFilter);
        return [
            'datasets' => [
            [
                'label' => 'Total Collection',
                'data' => $data,
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#9BD0F5',
            ],
        ],
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return OrderOfPayment::selectRaw('YEAR(created_at) as year')
                                ->distinct()
                                ->orderBy('year', 'desc')
                                ->pluck('year')
                                ->mapWithKeys(fn($yr) => [$yr => $yr])
                                ->toArray();
    }
}
