<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NormalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Gebruiker',
            'email_verified_at' => now(),
            'password' => Hash::make('user'),
            'role' => 'medewerker',
        ]);

        User::updateOrCreate([
            'email' => 'projectleider@example.com',
        ], [
            'name' => 'Projectleider',
            'email_verified_at' => now(),
            'password' => Hash::make('projectleider'),
            'role' => 'projectleider',
        ]);
    }
}
