<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Event;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        // Assign admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole);
        }
        $this->command->info('✓ Admin account created: admin@example.com / password');

        // Create organizer user
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Organizer User',
                'password' => Hash::make('password'),
            ]
        );

        // Assign organizer role
        $organizerRole = Role::where('slug', 'organizer')->first();
        if ($organizerRole && !$organizer->hasRole('organizer')) {
            $organizer->roles()->attach($organizerRole);
        }
        $this->command->info('✓ Organizer account created: organizer@example.com / password');

        // Create customer user
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
            ]
        );

        // Assign customer role
        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole && !$customer->hasRole('customer')) {
            $customer->roles()->attach($customerRole);
        }
        $this->command->info('✓ Customer account created: customer@example.com / password');

        // Ensure existing events have an owner: assign to organizer if missing
        $organizerUser = User::where('email', 'organizer@example.com')->first();
        if ($organizerUser) {
            Event::whereNull('user_id')->update(['user_id' => $organizerUser->id]);
        }

        // Set random tickets_available (100-900) for all events that don't have it set
        Event::where('tickets_available', 0)->orWhereNull('tickets_available')->chunkById(100, function ($events) {
            foreach ($events as $event) {
                $event->update(['tickets_available' => rand(100, 900)]);
            }
        });

        // Assign customer role to all users who don't have any roles
        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $usersWithoutRoles = User::whereDoesntHave('roles')->get();
            foreach ($usersWithoutRoles as $user) {
                $user->roles()->attach($customerRole);
                $this->command->info("✓ Assigned customer role to existing user: {$user->email}");
            }
        }
    }
}
