<?php

namespace App\Filament\Resources\LivestockResource\Widgets;

use App\Services\DashboardService;
use Filament\Widgets\ChartWidget;

class LivestockDistribution extends ChartWidget
{
    protected static ?string $heading = 'Livestock Distribution';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $dashboardService = new DashboardService();
        $distribution = $dashboardService->getLivestockDistribution();

        return [
            'datasets' => [
                [
                    'data' => $distribution['data'],
                ],
            ],
            'labels' => $distribution['types'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'display' => false,
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => false,
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
