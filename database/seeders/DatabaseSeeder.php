<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Only roles and login accounts are seeded by default — the system is
        // meant to start empty so real input surfaces its own bugs.
        //
        // The demo datasets still exist and are still exercised by their
        // tests (SeederValuationIntegrityTest, PulauObiEcosystemSeederTest);
        // run them by hand when a populated database is wanted:
        //   php artisan db:seed --class=SampleDataSeeder
        //   php artisan db:seed --class=RegionalValuationSeeder
        //   php artisan db:seed --class=PulauObiEcosystemSeeder
        $this->call([
            RoleAndUserSeeder::class,
        ]);
    }
}
