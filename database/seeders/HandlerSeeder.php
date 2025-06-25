<?php

namespace Database\Seeders;

use App\Models\Handler;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class HandlerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $handlers = ["Jose", "Arib", "David", "Christian"];
        $faker = Faker::create();

        for($i = 0; $i < count($handlers); $i++){
            Handler::create([
                'name' => $handlers[$i],
                'plate_no' => strtoupper($faker->bothify('?? ####'))
            ]);
        }
    }
}
