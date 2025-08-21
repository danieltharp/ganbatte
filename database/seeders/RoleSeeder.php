<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access including user management and system configuration'
            ],
            [
                'name' => 'developer',
                'display_name' => 'Developer',
                'description' => 'Can manage contributions, approve content, and commit changes to JSON files'
            ],
            [
                'name' => 'staff',
                'display_name' => 'Staff',
                'description' => 'Can manage contributions and moderate content'
            ],
            [
                'name' => 'trusted_contributor',
                'display_name' => 'Trusted Contributor',
                'description' => 'Can review and approve community contributions'
            ],
            [
                'name' => 'supporter',
                'display_name' => 'Supporter',
                'description' => 'Recognized community member who supports the platform'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']], 
                $roleData
            );
        }
    }
}
