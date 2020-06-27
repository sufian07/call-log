<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i=0; $i < 500; $i++) {
            \App\Call::create([
                'call_date' => date('Y-m-d', strtotime( '+'.mt_rand(0,30).' days')),
                'phone_number' => $faker->phoneNumber,
                'call_duration' =>  rand(0,23).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT),
                'status' => Arr::random(['in_call', 'hold', 'call_back', 'do_not_call']),
            ]);
        }
    }
}
