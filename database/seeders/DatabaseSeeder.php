<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            DestinationSeeder::class,
            TourSeeder::class,
            UserSeeder::class,
            ReviewSeeder::class,
            BookingSeeder::class,
            PageSectionSeeder::class,
        ]);
    }
}
