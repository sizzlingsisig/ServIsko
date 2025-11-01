<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            // Academic Skills
            [
                'name' => 'Tutoring',
                'description' => 'Academic subject tutoring',
            ],
            [
                'name' => 'Writing Assistance',
                'description' => 'Essay, paper, and writing help',
            ],
            [
                'name' => 'Research Help',
                'description' => 'Research and literature review',
            ],
            [
                'name' => 'Proofreading',
                'description' => 'Editing and proofreading documents',
            ],
            [
                'name' => 'Exam Preparation',
                'description' => 'Test and exam preparation',
            ],
            [
                'name' => 'Assignment Help',
                'description' => 'Homework and project assistance',
            ],

            // Technical Skills
            [
                'name' => 'Programming',
                'description' => 'Software development and coding',
            ],
            [
                'name' => 'Web Development',
                'description' => 'Website and web app creation',
            ],
            [
                'name' => 'Mobile Development',
                'description' => 'Mobile app development',
            ],
            [
                'name' => 'Database Management',
                'description' => 'Database design and SQL',
            ],
            [
                'name' => 'IT Support',
                'description' => 'Technical troubleshooting',
            ],

            // Design Skills
            [
                'name' => 'Graphic Design',
                'description' => 'Visual design and graphics',
            ],
            [
                'name' => 'UI/UX Design',
                'description' => 'User interface and experience design',
            ],
            [
                'name' => 'Logo Design',
                'description' => 'Logo and brand design',
            ],
            [
                'name' => 'Video Editing',
                'description' => 'Video production and editing',
            ],
            [
                'name' => 'Photography',
                'description' => 'Photo taking and editing',
            ],

            // Communication Skills
            [
                'name' => 'Public Speaking',
                'description' => 'Presentation and speaking skills',
            ],
            [
                'name' => 'Content Writing',
                'description' => 'Writing and content creation',
            ],
            [
                'name' => 'Translation',
                'description' => 'Language translation services',
            ],
            [
                'name' => 'Copywriting',
                'description' => 'Marketing and persuasive writing',
            ],

            // Business Skills
            [
                'name' => 'Digital Marketing',
                'description' => 'Online marketing and advertising',
            ],
            [
                'name' => 'Social Media Management',
                'description' => 'Social media strategy and posting',
            ],
            [
                'name' => 'Business Planning',
                'description' => 'Business strategy and planning',
            ],
            [
                'name' => 'Project Management',
                'description' => 'Project coordination and planning',
            ],

            // Creative Skills
            [
                'name' => 'Music Instruction',
                'description' => 'Music lessons and instruction',
            ],
            [
                'name' => 'Dance Instruction',
                'description' => 'Dance classes and training',
            ],
            [
                'name' => 'Art & Drawing',
                'description' => 'Artistic drawing and painting',
            ],
            [
                'name' => 'Animation',
                'description' => 'Animation and motion graphics',
            ],

            // Language Skills
            [
                'name' => 'Language Teaching',
                'description' => 'Teaching foreign languages',
            ],
            [
                'name' => 'English Tutoring',
                'description' => 'English language instruction',
            ],
            [
                'name' => 'Filipino Tutoring',
                'description' => 'Filipino language instruction',
            ],

            // Administrative Skills
            [
                'name' => 'Virtual Assistance',
                'description' => 'Administrative and support tasks',
            ],
            [
                'name' => 'Data Entry',
                'description' => 'Data organization and entry',
            ],
            [
                'name' => 'Document Preparation',
                'description' => 'Typing and document formatting',
            ],
            [
                'name' => 'Bookkeeping',
                'description' => 'Accounting and financial tracking',
            ],

            // Health & Wellness Skills
            [
                'name' => 'Fitness Training',
                'description' => 'Personal training and coaching',
            ],
            [
                'name' => 'Yoga Instruction',
                'description' => 'Yoga and flexibility training',
            ],
            [
                'name' => 'Wellness Coaching',
                'description' => 'Health and nutrition coaching',
            ],
            [
                'name' => 'Mental Health Support',
                'description' => 'Peer counseling and support',
            ],

            // Organization & Planning
            [
                'name' => 'Event Planning',
                'description' => 'Event organization and coordination',
            ],
            [
                'name' => 'Study Group Facilitation',
                'description' => 'Organizing study sessions',
            ],
            [
                'name' => 'Time Management',
                'description' => 'Planning and time management',
            ],

            // Career & Development
            [
                'name' => 'Resume Writing',
                'description' => 'Resume and CV preparation',
            ],
            [
                'name' => 'Interview Coaching',
                'description' => 'Job interview preparation',
            ],
            [
                'name' => 'Career Counseling',
                'description' => 'Career guidance and planning',
            ],

            // Software & Tools
            [
                'name' => 'Microsoft Office',
                'description' => 'Word, Excel, PowerPoint training',
            ],
            [
                'name' => 'Spreadsheet Skills',
                'description' => 'Data analysis and spreadsheets',
            ],
            [
                'name' => 'Canva Design',
                'description' => 'Canva graphic design',
            ],

            // Miscellaneous
            [
                'name' => 'Consulting',
                'description' => 'General consulting services',
            ],
            [
                'name' => 'Freelance Work',
                'description' => 'Various freelance services',
            ],
        ];

        foreach ($skills as $skill) {
            Skill::create($skill);
        }
    }
}
