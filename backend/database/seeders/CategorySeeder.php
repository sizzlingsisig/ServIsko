<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Academic Tutoring',
                'description' => 'One-on-one or group tutoring for various academic subjects including Math, Science, English, Filipino, and more.',
            ],
            [
                'name' => 'Programming & Tech',
                'description' => 'Web development, mobile app development, software programming, debugging, and technical support services.',
            ],
            [
                'name' => 'Graphic Design',
                'description' => 'Logo design, poster creation, infographics, digital art, social media graphics, and visual content creation.',
            ],
            [
                'name' => 'Music & Audio',
                'description' => 'Music lessons, audio editing, sound mixing, voice-over services, and instrument tutoring.',
            ],
            [
                'name' => 'Writing & Translation',
                'description' => 'Content writing, copywriting, translation, proofreading, and editing services.',
            ],
            [
                'name' => 'Business & Finance',
                'description' => 'Business consulting, accounting, bookkeeping, and financial planning.',
            ],
            [
                'name' => 'Health & Wellness',
                'description' => 'Fitness coaching, nutrition advice, mental health support, and wellness programs.',
            ],
            [
                'name' => 'Home & Lifestyle',
                'description' => 'Home organization, cleaning, gardening, and lifestyle coaching.',
            ],
            [
                'name' => 'Events & Entertainment',
                'description' => 'Event planning, hosting, emceeing, and entertainment services for all occasions.',
            ],
            [
                'name' => 'Photography & Video',
                'description' => 'Photography, videography, video editing, and photo retouching services.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info('Created ' . count($categories) . ' categories successfully!');
    }
}
