<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roleAdmin = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access'
        ]);

        $roleSurveyor = Role::create([
            'name' => 'Surveyor',
            'slug' => 'surveyor',
            'description' => 'Data entry only - TCM, CVM, EOP input'
        ]);

        $roleAnalyst = Role::create([
            'name' => 'Analyst',
            'slug' => 'analyst',
            'description' => 'Data verification and reporting'
        ]);

        // Create Admin User
        User::create([
            'name' => 'Admin Valuasi',
            'email' => 'admin@valuasi.local',
            'email_verified_at' => now(),
            'password' => Hash::make('admin@123'),
            'role_id' => $roleAdmin->id,
            'phone' => '08123456789',
            'institution' => 'Valuasi Ekonomi Admin',
            'is_active' => true,
        ]);

        // Create Surveyor Users
        User::create([
            'name' => 'Surveyor 1',
            'email' => 'surveyor1@valuasi.local',
            'email_verified_at' => now(),
            'password' => Hash::make('surveyor@123'),
            'role_id' => $roleSurveyor->id,
            'phone' => '08111111111',
            'institution' => 'Survey Team A',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Surveyor 2',
            'email' => 'surveyor2@valuasi.local',
            'email_verified_at' => now(),
            'password' => Hash::make('surveyor@123'),
            'role_id' => $roleSurveyor->id,
            'phone' => '08111111112',
            'institution' => 'Survey Team B',
            'is_active' => true,
        ]);

        // Create Analyst Users
        User::create([
            'name' => 'Analyst 1',
            'email' => 'analyst1@valuasi.local',
            'email_verified_at' => now(),
            'password' => Hash::make('analyst@123'),
            'role_id' => $roleAnalyst->id,
            'phone' => '08222222222',
            'institution' => 'Analysis Team',
            'is_active' => true,
        ]);
    }
}
