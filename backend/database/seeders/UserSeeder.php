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
            'location' => 'Barangay Ubos, Miagao, Iloilo',
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
            'location' => 'UPV Miagao Campus, Miagao, Iloilo',
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
            'location' => 'Barangay Poblacion, Miagao, Iloilo',
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

        // Additional users (4-10)
        $users = [
            [
                'name' => 'Maria Tutor',
                'username' => 'maria_tutor',
                'email' => 'maria.tutor@example.com',
                'roles' => ['service-provider'],
                'bio' => 'Math and science tutor with 5 years experience.',
                'location' => 'Barangay Mat-y, Miagao, Iloilo',
                'skills' => [1], // PHP
            ],
            [
                'name' => 'Carlos Designer',
                'username' => 'carlos_designer',
                'email' => 'carlos.designer@example.com',
                'roles' => ['service-provider'],
                'bio' => 'Freelance graphic designer and digital artist.',
                'location' => 'Barangay Kirayan Norte, Miagao, Iloilo',
                'skills' => [2, 3], // Vue.js, Laravel
            ],
            [
                'name' => 'Liza Seeker',
                'username' => 'liza_seeker',
                'email' => 'liza.seeker@example.com',
                'roles' => ['service-seeker'],
                'bio' => 'Looking for help with web development projects.',
                'location' => 'Barangay Kirayan Sur, Miagao, Iloilo',
                'skills' => [],
            ],
            [
                'name' => 'Ben Provider',
                'username' => 'ben_provider',
                'email' => 'ben.provider@example.com',
                'roles' => ['service-provider'],
                'bio' => 'Offers JavaScript and Laravel services.',
                'location' => 'Barangay Tacas, Miagao, Iloilo',
                'skills' => [3, 4], // Laravel, JavaScript
            ],
            [
                'name' => 'Anna Admin',
                'username' => 'anna_admin',
                'email' => 'anna.admin@example.com',
                'roles' => ['admin', 'service-seeker'],
                'bio' => 'Admin and project manager.',
                'location' => 'UPV Academic Zone, Miagao, Iloilo',
                'skills' => [],
            ],
            [
                'name' => 'Rico Fullstack',
                'username' => 'rico_fullstack',
                'email' => 'rico.fullstack@example.com',
                'roles' => ['service-provider', 'service-seeker'],
                'bio' => 'Fullstack dev, open to new projects.',
                'location' => 'Barangay Bolho, Miagao, Iloilo',
                'skills' => [1, 2, 3, 4, 5],
            ],
            [
                'name' => 'Grace Writer',
                'username' => 'grace_writer',
                'email' => 'grace.writer@example.com',
                'roles' => ['service-seeker'],
                'bio' => 'Needs help with content and editing.',
                'location' => 'Barangay Baybay Norte, Miagao, Iloilo',
                'skills' => [],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole($userData['roles']);
            $user->profile()->create([
                'bio' => $userData['bio'],
                'location' => $userData['location'],
            ]);
            if (in_array('service-provider', $userData['roles'])) {
                $providerProfile = $user->providerProfile()->create();
                if (!empty($userData['skills'])) {
                    $providerProfile->skills()->attach($userData['skills']);
                }
            }
        }
    }
}
