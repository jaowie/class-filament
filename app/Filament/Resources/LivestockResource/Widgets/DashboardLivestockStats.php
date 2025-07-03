<?php

namespace App\Filament\Resources\LivestockResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Livestock;
use App\Models\OrderOfPayment;
use App\Services\DashboardService;

class DashboardLivestockStats extends BaseWidget
{
    public ?Model $record = null;
    protected function getStats(): array
    {
        $dashboardService = new DashboardService();

        return [
            Stat::make('Released Today', '2')
                ->icon('heroicon-o-arrow-up'),
            Stat::make('Received Today', $dashboardService->getReceivedToday())
                ->icon('heroicon-o-arrow-down'),
            Stat::make('Today\'s Total Collection', '₱ ' . $dashboardService->getTotalCollection())
                ->icon('heroicon-o-building-library')
        ];
    }
}
