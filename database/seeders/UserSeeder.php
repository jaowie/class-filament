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
        $superAdmin->assignRole('super_admin');

        $cvoUser = User::create([
            'name' => 'cvo',
            'first_name' => 'CVO',
            'last_name' => 'User',
            'email' => 'cvo@citc.com',
            'employee_number' => '000002',
            'position_title' => 'City Veterinarian Office',
            'password' => Hash::make('password'),
        ]);
        $cvoUser->assignRole('CVO');

        $ceeUser = User::create([
            'name' => 'cee',
            'first_name' => 'CEE',
            'last_name' => 'User',
            'email' => 'cee@citc.com',
            'employee_number' => '000003',
            'position_title' => 'City Economic Enterprise',
            'password' => Hash::make('password'),
        ]);
        $ceeUser->assignRole('CEE');
    }
}
