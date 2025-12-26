<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            ['name' => 'View Events', 'slug' => 'view-events', 'description' => 'Can view events'],
            ['name' => 'Create Events', 'slug' => 'create-events', 'description' => 'Can create new events'],
            ['name' => 'Edit Events', 'slug' => 'edit-events', 'description' => 'Can edit events'],
            ['name' => 'Delete Events', 'slug' => 'delete-events', 'description' => 'Can delete events'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'Can manage users and their roles'],
            ['name' => 'Purchase Tickets', 'slug' => 'purchase-tickets', 'description' => 'Can purchase event tickets'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access',
                'permissions' => ['view-events', 'create-events', 'edit-events', 'delete-events', 'manage-users', 'purchase-tickets'],
            ],
            [
                'name' => 'Organizer',
                'slug' => 'organizer',
                'description' => 'Can create and manage events',
                'permissions' => ['view-events', 'create-events', 'edit-events', 'purchase-tickets'],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Can view events and purchase tickets',
                'permissions' => ['view-events', 'purchase-tickets'],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Attach permissions to role
            $permissionModels = Permission::whereIn('slug', $permissions)->get();
            $role->permissions()->sync($permissionModels->pluck('id'));
        }
    }
}
