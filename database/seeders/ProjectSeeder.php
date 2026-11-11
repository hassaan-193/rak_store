<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create dummy projects
        Project::create([
            'name' => 'Project Alpha',
            'description' => 'A description for Project Alpha.',
        ]);

        Project::create([
            'name' => 'Project Beta',
            'description' => 'A description for Project Beta.',
        ]);

        Project::create([
            'name' => 'Project Gamma',
            'description' => 'A description for Project Gamma.',
        ]);

        Project::create([
            'name' => 'Project Delta',
            'description' => 'A description for Project Delta.',
        ]);

        Project::create([
            'name' => 'Project Epsilon',
            'description' => 'A description for Project Epsilon.',
        ]);
    }
}
