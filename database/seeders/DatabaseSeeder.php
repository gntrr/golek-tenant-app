<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DemoEventSeeder;
use Database\Seeders\DemoOrderSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin account
        // Disabled, use "php artisan make:filament-user"

        // \App\Models\User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => bcrypt('password'),
        // ]);

        // Create demo events
        $this->call(DemoEventSeeder::class);

        // Create demo orders
        $this->call(DemoOrderSeeder::class);
    }
}
