<?php

namespace ApurbaLabs\IAM\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IAMTestDataSeeder::class, 
        ]);
    }
}
