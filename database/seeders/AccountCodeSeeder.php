<?php

namespace Database\Seeders;

use App\Models\AccountCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountCodes = [
            [
            'account_code' => '40201990-14-2',
            'description' => 'Ante and Post Mortem - CVO',
            'hog_amount' => '15.00',
            'cattle_amount' => '25.00',
            'goat_amount' => '134.00',
            ],
            [
            'account_code' => '40201010-14-1',
            'description' => 'Permit Fees to Slaughter-CVO',
            'hog_amount' => '50.00',
            'cattle_amount' => '100.00',
            'goat_amount' => '20.00',
            ],
            [
            'account_code' => '40202150-20-81-1',
            'description' => 'Slaughter Fee - 60% govt share - Econ. Enterprise/Maa',
            'hog_amount' => '268.03',
            'cattle_amount' => '402.02',
            'goat_amount' => '134.02',
            ],
            [
            'account_code' => '40202150-20-81-3', 
            'description' => 'Corral Fees - 40% govt share- Econ. Enterprise/Maa',
            'hog_amount' => '20.00',
            'cattle_amount' => '50.00',
            'goat_amount' => '10.00'
            ],
            [
            'account_code' => '40202150-20-81-6', 
            'description' => 'Washing Fee - Slaughterhouse- Econ. Enterprise/Maa',
            'hog_amount' => '10.00',
            'cattle_amount' => '10.00',
            'goat_amount' => '10.00'
            ],
            [
            'account_code' => '12345', 
            'description' => 'Weighing Fee - Slaughterhouse- Econ. Enterprise/Maa',
            'hog_amount' => '20.00',
            'cattle_amount' => '40.00',
            'goat_amount' => '10.00'
            ],
            [
            'account_code' => '40202150-20-81-11', 
            'description' => 'Chilling Facility Fee for Goats/Sheep - Slaughterhouse- Econ. Enterprise/Maa',
            'amount' => '100.00'
            ],

            [
            'account_code' => '40202150-20-81-10', 
            'description' => 'Chilling Facility Fee for Hogs - Slaughterhouse- Econ. Enterprise/Maa',
            'amount' => '100.00'
            ],
            [
            'account_code' => '40202150-20-81-9', 
            'description' => 'Chilling Facility Fee for Large Cattle - Slaughterhouse- Econ. enterprise/Maa',
            'amount' => '200.00'
            ],
 
        ];

        foreach ($accountCodes as $code) {
            AccountCode::create($code);
        }
    }
}
