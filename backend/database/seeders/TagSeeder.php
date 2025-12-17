<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Math'],
            ['name' => 'Science'],
            ['name' => 'Programming'],
            ['name' => 'Design'],
            ['name' => 'Music'],
            ['name' => 'Tutoring'],
            ['name' => 'Laravel'],
            ['name' => 'Vue.js'],
            ['name' => 'Logo'],
            ['name' => 'Guitar'],
            ['name' => 'Fitness'],
            ['name' => 'Nutrition'],
            ['name' => 'Translation'],
            ['name' => 'Copywriting'],
            ['name' => 'Editing'],
            ['name' => 'Accounting'],
            ['name' => 'Bookkeeping'],
            ['name' => 'Photography'],
            ['name' => 'Video Editing'],
            ['name' => 'Event Planning'],
            ['name' => 'Social Media'],
            ['name' => 'Digital Art'],
            ['name' => 'Web Development'],
            ['name' => 'Mobile Apps'],
            ['name' => 'Business'],
            ['name' => 'Finance'],
            ['name' => 'Wellness'],
            ['name' => 'Gardening'],
            ['name' => 'Cleaning'],
            ['name' => 'Emceeing'],
        ];
        foreach ($tags as $tag) {
            Tag::updateOrCreate(['name' => $tag['name']], $tag);
        }
    }
}
