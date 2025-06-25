<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $superAdmin = User::create([
            'name' => 'citcadmin',
            'first_name' => 'CITC',
            'last_name' => 'Admin',
            'email' => 'super@citc.com',
            'employee_number' => '000001',
            'position_title' => 'gold lane',
            'password' => Hash::make('password'),
        ]);
        // $superAdmin->assignRole('Superadmin');
    }
}
