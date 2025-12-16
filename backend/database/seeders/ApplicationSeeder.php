<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\Listing;
use App\Models\User;

class ApplicationSeeder extends Seeder
{
	public function run(): void
	{
		// Only allow service providers to submit applications
		$users = User::role('service-provider')->get();
		$listings = Listing::all();
		$statuses = ['pending', 'accepted', 'rejected'];

		$applications = [];

		// For each listing, create 2-3 applications from different users
		foreach ($listings as $listing) {
			$applicants = $users->where('id', '!=', $listing->seeker_user_id)->random(min(3, $users->count() - 1));
			foreach ($applicants as $user) {
				$applications[] = [
					'user_id' => $user->id,
					'listing_id' => $listing->id,
					'status' => $statuses[array_rand($statuses)],
					'message' => 'Application for listing #' . $listing->id . ' by user #' . $user->id,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
		}

		// Insert all applications in bulk
		if (!empty($applications)) {
			Application::insert($applications);
		}

		$this->command->info('Applications seeded successfully!');
	}
}
