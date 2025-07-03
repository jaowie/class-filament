<?php


namespace App\Services;

use App\Models\Livestock;
use App\Models\OrderOfPayment;

class DashboardService {

    public function getLivestockDistribution() {
        $data = [];

        $livestockTypes = Livestock::distinct()->pluck('type')->toArray();

        foreach ($livestockTypes as $livestockType){
            $data[] = Livestock::where('type', $livestockType)->count();
        }

        return [
            'types' => $livestockTypes,
            'data' => $data
        ];
    }

    public function getReceivedToday(){
        return Livestock::whereDate('created_at', now()->toDateString())->where('status', 'Received')->count();
    }

    public function getTotalCollection(){
        return OrderOfPayment::whereDate('created_at', now()->toDateString())->where('status', 'Paid')->sum('amount');
    }

    public function getYearlyTotalCollection($year){
        $data = [];

        for($i=1; $i <= 12; $i++){
            $data[] = OrderOfPayment::whereMonth('created_at', "$i")
                                    ->whereYear('created_at', $year)
                                    ->where('status', 'Paid')
                                    ->sum('amount');
        }
        
        return $data;
    }

}