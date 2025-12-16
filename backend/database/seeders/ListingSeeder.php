<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
{
    // Generate 25 listings with varied categories, tags, and applications
    $categoryCount = \App\Models\Category::count();
    $userCount = \App\Models\User::count();
    $tagIdsAll = \App\Models\Tag::pluck('id')->toArray();
    $titles = [
        'Math Tutoring for High School', 'Web Development Project Help', 'Logo Design Needed', 'Guitar Lessons',
        'Nutrition Advice for Athletes', 'Business Plan Review', 'Fitness Coaching', 'Event Planning for Birthdays',
        'Digital Art Commission', 'Mobile App Debugging', 'Bookkeeping Assistance', 'Social Media Graphics',
        'Copywriting for Websites', 'Translation (English-Filipino)', 'Photography for Graduation', 'Video Editing for Vlogs',
        'Home Organization Service', 'Emceeing for Weddings', 'Science Project Guidance', 'Music Production',
        'Poster Design', 'Accounting Help', 'Wellness Coaching', 'Gardening Tips', 'Cleaning Service'
    ];
    $descriptions = [
        'Offering expert help and flexible schedule.', 'Looking for experienced professionals.', 'Creative and reliable service.',
        'One-on-one or group sessions available.', 'Affordable and high quality.', 'Fast turnaround and great communication.',
        'Seeking passionate and skilled providers.', 'Open to custom requests.', 'For students and professionals alike.',
        'Remote and in-person options.', 'Let us help you succeed!', 'Contact for more details.'
    ];
    for ($i = 0; $i < 25; $i++) {
        // Make admin_user (id=3) create listings in every category
        $seekerId = ($i % 5 === 0) ? 3 : (($i % $userCount) + 1); // admin_user creates every 5th listing
        $listingData = [
            'title' => $titles[$i % count($titles)],
            'description' => $descriptions[array_rand($descriptions)],
            'budget' => rand(500, 5000),
            'category_id' => ($i % $categoryCount) + 1,
            'seeker_user_id' => $seekerId,
            'status' => (rand(0, 10) > 1) ? 'active' : 'expired',
        ];
        $listing = \App\Models\Listing::create($listingData);
        // Attach 2-4 random tags
        shuffle($tagIdsAll);
        $listing->tags()->attach(array_slice($tagIdsAll, 0, rand(2, 4)));
        // Only allow service providers to apply
        $providerIds = \App\Models\User::role('service-provider')->pluck('id')->toArray();
        // Remove the seeker from applicants
        $applicantIds = array_diff($providerIds, [$seekerId]);
        shuffle($applicantIds);
        $numApps = rand(2, 4);
        $hasAccepted = false;
        $adminApplied = false;
        for ($j = 0; $j < $numApps; $j++) {
            // Always make admin_user apply to at least 1/2 of listings not their own
            if (!$adminApplied && $seekerId !== 3 && ($j === 0 || rand(0, 1))) {
                $appUserId = 3;
                $adminApplied = true;
            } else {
                $appUserId = $applicantIds[$j % count($applicantIds)] ?? null;
                if ($appUserId === 3 || $appUserId === null) continue;
            }
            $status = ['pending', 'accepted', 'rejected'][array_rand(['pending', 'accepted', 'rejected'])];
            if ($status === 'accepted') {
                $hasAccepted = true;
            }
            \App\Models\Application::create([
                'user_id' => $appUserId,
                'listing_id' => $listing->id,
                'status' => $status,
                'message' => 'Application for ' . $listingData['title'] . ' by user #' . $appUserId,
            ]);
        }
        // If any application is accepted, close the listing
        if ($hasAccepted) {
            $listing->status = 'closed';
            $listing->save();
        }
    }
    $this->command->info('25 listings with varied tags, categories, and applications seeded successfully!');
}
}
