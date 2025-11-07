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

        $providerProfile = $seekerProvider->providerProfile()->create();

        // Add links to provider profile
        $providerProfile->links()->createMany([
            [
                'title' => 'GitHub',
                'url' => 'https://github.com/johndeveloper',
                'order' => 0,
            ],
            [
                'title' => 'Portfolio',
                'url' => 'https://portfolio.example.com',
                'order' => 1,
            ],
        ]);

        // Attach skills to provider profile
        $providerProfile->skills()->attach([1, 2, 3]);

        // User 3: Admin + Seeker + Provider
        $adminSeekerProvider = User::create([
            'name' => 'Admin User',
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $adminSeekerProvider->assignRole(['admin', 'service-seeker', 'service-provider']);

        $adminSeekerProvider->profile()->create([
            'bio' => 'Platform admin, experienced developer, and passionate educator',
            'location' => 'Miagao, Western Visayas',
            'profile_picture' => null,
        ]);

        $adminProviderProfile = $adminSeekerProvider->providerProfile()->create();

        // Add links to admin provider profile
        $adminProviderProfile->links()->createMany([
            [
                'title' => 'GitHub',
                'url' => 'https://github.com/admin',
                'order' => 0,
            ],
            [
                'title' => 'LinkedIn',
                'url' => 'https://linkedin.com/in/admin',
                'order' => 1,
            ],
            [
                'title' => 'Portfolio',
                'url' => 'https://portfolio.example.com',
                'order' => 2,
            ],
        ]);

        // Attach all skills to admin provider profile
        $adminProviderProfile->skills()->attach([1, 2, 3, 4, 5]);
    }
}
