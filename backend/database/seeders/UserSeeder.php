<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        // Create some skills first
        $skills = [
            ['name' => 'PHP', 'description' => 'Backend development with PHP'],
            ['name' => 'Vue.js', 'description' => 'Frontend framework'],
            ['name' => 'Laravel', 'description' => 'PHP framework'],
            ['name' => 'JavaScript', 'description' => 'Client-side scripting'],
            ['name' => 'PostgreSQL', 'description' => 'Database management'],
        ];

        foreach ($skills as $skill) {
            \App\Models\Skill::create($skill);
        }

        // User 1: Seeker Only
        $seeker = User::create([
            'name' => 'Jane Seeker',
            'username' => 'jane_seeker',
            'email' => 'seeker@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $seeker->assignRole('service-seeker');

        $seeker->profile()->create([
            'bio' => 'Looking for professional tutoring services in programming',
            'location' => 'Miagao, Western Visayas',
            'profile_picture' => null,
        ]);

        // User 2: Seeker + Provider
        $seekerProvider = User::create([
            'name' => 'John Developer',
            'username' => 'john_developer',
            'email' => 'seekerprovider@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $seekerProvider->assignRole(['service-seeker', 'service-provider']);

        $seekerProvider->profile()->create([
            'bio' => 'Developer seeking advanced courses and offering tutoring',
            'location' => 'Iloilo City, Western Visayas',
            'profile_picture' => null,
        ]);

        $seekerProvider->providerProfile()->create([
            'links' => [
                'https://github.com/johndeveloper',
                'https://portfolio.example.com',
            ],
        ]);

        // Attach skills to provider profile
        $seekerProvider->providerProfile->skills()->attach([1, 2, 3]);

        // User 3: Admin + Seeker + Provider
        $adminSeekerprovider = User::create([
            'name' => 'Admin User',
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $adminSeekerprovider->assignRole(['admin', 'service-seeker', 'service-provider']);

        $adminSeekerprovider->profile()->create([
            'bio' => 'Platform admin, experienced developer, and passionate educator',
            'location' => 'Miagao, Western Visayas',
            'profile_picture' => null,
        ]);

        $adminSeekerprovider->providerProfile()->create([
            'links' => [
                'https://github.com/admin',
                'https://linkedin.com/in/admin',
                'https://portfolio.example.com',
            ],
        ]);

        // Attach all skills to admin provider profile
        $adminSeekerprovider->providerProfile->skills()->attach([1, 2, 3, 4, 5]);
    }
}
