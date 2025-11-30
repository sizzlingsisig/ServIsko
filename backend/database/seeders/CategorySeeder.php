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
            // Academic & Educational
            [
                'name' => 'Academic Tutoring',
                'description' => 'One-on-one or group tutoring for various academic subjects including Math, Science, English, Filipino, and more.',
            ],
            [
                'name' => 'Research Assistance',
                'description' => 'Help with research projects, data collection and analysis, literature review, and thesis support.',
            ],
            [
                'name' => 'Study Groups',
                'description' => 'Organize or join study groups, exam review sessions, and collaborative learning opportunities.',
            ],
            [
                'name' => 'Language Learning',
                'description' => 'Language tutoring for English, Filipino, Spanish, Japanese, Korean, and other foreign languages.',
            ],
            [
                'name' => 'Test Preparation',
                'description' => 'Preparation services for entrance exams, board exams, IELTS, TOEFL, and other standardized tests.',
            ],

            // Technology & Digital
            [
                'name' => 'Programming & Tech',
                'description' => 'Web development, mobile app development, software programming, debugging, and technical support services.',
            ],
            [
                'name' => 'Computer Repair',
                'description' => 'Laptop and desktop repair, hardware troubleshooting, software installation, and tech maintenance.',
            ],
            [
                'name' => 'Digital Marketing',
                'description' => 'Social media management, content marketing, SEO services, online advertising, and brand promotion.',
            ],
            [
                'name' => 'Gaming & Streaming',
                'description' => 'Gaming coaching, live stream setup, content creation for gaming, and esports consulting.',
            ],

            // Creative & Design
            [
                'name' => 'Graphic Design',
                'description' => 'Logo design, poster creation, infographics, digital art, social media graphics, and visual content creation.',
            ],
            [
                'name' => 'Video & Photography',
                'description' => 'Event photography, video editing, videography, photo retouching, and multimedia content creation.',
            ],
            [
                'name' => 'Arts & Crafts',
                'description' => 'Handmade products, custom artwork, craft workshops, DIY projects, and creative services.',
            ],
            [
                'name' => 'Music & Audio',
                'description' => 'Music lessons, audio editing, sound mixing, voice-over services, and instrument tutoring.',
            ],
            [
                'name' => '3D Modeling & Animation',
                'description' => '3D modeling, animation services, rendering, and visual effects for projects and presentations.',
            ],

            // Writing & Content
            [
                'name' => 'Writing & Translation',
                'description' => 'Content writing, editing, proofreading, translation services, academic writing, and research assistance.',
            ],
            [
                'name' => 'Resume & CV Writing',
                'description' => 'Professional resume writing, CV formatting, cover letter creation, and LinkedIn profile optimization.',
            ],
            [
                'name' => 'Content Creation',
                'description' => 'Blog writing, social media content, copywriting, scriptwriting, and creative content production.',
            ],

            // Business & Professional
            [
                'name' => 'Consulting & Advice',
                'description' => 'Academic consulting, career advice, business consulting, mentorship services, and guidance.',
            ],
            [
                'name' => 'Data Entry & Admin',
                'description' => 'Data entry, virtual assistance, document organization, transcription, and administrative support.',
            ],
            [
                'name' => 'Accounting & Finance',
                'description' => 'Bookkeeping assistance, financial planning, budget consulting, and basic accounting services.',
            ],
            [
                'name' => 'Legal Services',
                'description' => 'Document drafting, legal research, contract review, and basic legal consultation for students.',
            ],
            [
                'name' => 'Business Planning',
                'description' => 'Business plan development, market research, feasibility studies, and startup consulting.',
            ],

            // Events & Entertainment
            [
                'name' => 'Event Services',
                'description' => 'Event planning, hosting, emceeing, coordination, and support for university events and celebrations.',
            ],
            [
                'name' => 'Entertainment Services',
                'description' => 'Performance services, magic shows, comedy acts, live music, and entertainment for events.',
            ],
            [
                'name' => 'DJ & Sound System',
                'description' => 'DJ services, sound system rental, audio equipment setup for parties and events.',
            ],

            // Health & Wellness
            [
                'name' => 'Fitness & Wellness',
                'description' => 'Personal training, workout coaching, yoga instruction, nutrition consulting, and wellness guidance.',
            ],
            [
                'name' => 'Mental Health Support',
                'description' => 'Peer counseling, stress management coaching, meditation guidance, and emotional support.',
            ],
            [
                'name' => 'Sports Coaching',
                'description' => 'Personal coaching for basketball, volleyball, badminton, swimming, and other sports.',
            ],

            // Fashion & Beauty
            [
                'name' => 'Fashion & Beauty',
                'description' => 'Makeup services, hairstyling, fashion consultation, wardrobe styling, and beauty services.',
            ],
            [
                'name' => 'Tailoring & Alterations',
                'description' => 'Clothing alterations, custom tailoring, costume making, and garment repair services.',
            ],
            [
                'name' => 'Personal Shopping',
                'description' => 'Personal shopping assistance, wardrobe consultation, and style advisory services.',
            ],

            // Food & Culinary
            [
                'name' => 'Food & Catering',
                'description' => 'Homemade food delivery, meal prep services, baking, and small-scale catering for events.',
            ],
            [
                'name' => 'Cooking Lessons',
                'description' => 'Cooking tutorials, recipe development, meal planning, and culinary skill training.',
            ],
            [
                'name' => 'Baking & Pastry',
                'description' => 'Custom cakes, pastries, desserts, and baked goods for events and personal orders.',
            ],

            // Campus Services
            [
                'name' => 'Campus Errands',
                'description' => 'Campus errands, delivery services, queue assistance, document processing, and general task help.',
            ],
            [
                'name' => 'Printing Services',
                'description' => 'Document printing, binding, laminating, photocopying, and other printing-related services.',
            ],
            [
                'name' => 'Laundry Services',
                'description' => 'Laundry washing, ironing, dry cleaning pickup and delivery for busy students.',
            ],
            [
                'name' => 'Cleaning Services',
                'description' => 'Dorm room cleaning, apartment cleaning, organization services, and deep cleaning.',
            ],

            // Transportation & Logistics
            [
                'name' => 'Transportation Services',
                'description' => 'Ride-sharing, carpool coordination, airport transfers, and local transportation assistance.',
            ],
            [
                'name' => 'Delivery Services',
                'description' => 'Package delivery, food delivery, document courier, and general delivery services within campus.',
            ],
            [
                'name' => 'Moving & Packing',
                'description' => 'Help with moving, packing services, furniture assembly, and relocation assistance.',
            ],

            // Rentals & Sales
            [
                'name' => 'Equipment Rental',
                'description' => 'Rent cameras, laptops, projectors, sports equipment, and other items for short-term use.',
            ],
            [
                'name' => 'Second-Hand Marketplace',
                'description' => 'Buy and sell used textbooks, gadgets, furniture, and other student essentials.',
            ],
            [
                'name' => 'Room & Housing',
                'description' => 'Roommate matching, boarding house listings, apartment hunting assistance, and housing advice.',
            ],

            // Specialized Services
            [
                'name' => 'Pet Care',
                'description' => 'Pet sitting, dog walking, pet grooming, and animal care services for busy students.',
            ],
            [
                'name' => 'Plant Care',
                'description' => 'Plant sitting, gardening advice, succulent sales, and indoor plant maintenance.',
            ],
            [
                'name' => 'Gift Services',
                'description' => 'Custom gift baskets, gift wrapping, personalized gifts, and special occasion arrangements.',
            ],
            [
                'name' => 'Car Wash & Detailing',
                'description' => 'Mobile car wash, interior cleaning, car detailing, and vehicle maintenance services.',
            ],
            [
                'name' => 'Bike Repair',
                'description' => 'Bicycle maintenance, repair services, tire replacement, and bike customization.',
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
