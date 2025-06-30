<?php

namespace Database\Seeders;

use App\Models\Handler;
use App\Models\HandlerPlateNumber;
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

        foreach ($handlers as $name) {
            $handler = Handler::create([
                'name' => $name,
            ]);

            // Create 3 plate numbers for each handler
            for ($j = 0; $j < 3; $j++) {
                HandlerPlateNumber::create([
                    'handler_id' => $handler->id,
                    'plate_no' => strtoupper($faker->bothify('?? ####')),
                ]);
            }
        }
    }
}
