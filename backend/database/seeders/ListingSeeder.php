<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
{
    // Create 20 active listings
    Listing::factory()
        ->count(20)
        ->create()
        ->each(function ($listing) {
            if (Tag::exists()) {
                $listing->tags()->attach(
                    Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')
                );
            }
        });

    // Create 5 closed listings (with hired user)
    Listing::factory()
        ->count(5)
        ->closed()
        ->create()
        ->each(function ($listing) {
            if (Tag::exists()) {
                $listing->tags()->attach(
                    Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')
                );
            }
        });

    // Create 5 expired listings
    Listing::factory()
        ->count(5)
        ->expired()
        ->create()
        ->each(function ($listing) {
            if (Tag::exists()) {
                $listing->tags()->attach(
                    Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')
                );
            }
        });

    $this->command->info('Listings seeded successfully!');
}

}
