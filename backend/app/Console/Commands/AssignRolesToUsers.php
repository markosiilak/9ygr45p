<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AssignRolesToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles 
                            {--default=customer : Default role to assign to users without roles}
                            {--force : Force reassignment even if user already has roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign roles to all users in the database who do not have roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Assigning roles to users...');
        $this->newLine();

        // Get default role
        $defaultRoleSlug = $this->option('default');
        $defaultRole = Role::where('slug', $defaultRoleSlug)->first();

        if (!$defaultRole) {
            $this->error("Role '{$defaultRoleSlug}' not found!");
            $this->info('Available roles:');
            $roles = Role::all();
            foreach ($roles as $role) {
                $this->line("  - {$role->slug} ({$role->name})");
            }
            return 1;
        }

        $force = $this->option('force');
        $users = User::with('roles')->get();
        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            $hasRoles = $user->roles->count() > 0;

            if ($hasRoles && !$force) {
                $this->line("  ⏭  Skipping {$user->email} (already has roles: " . $user->roles->pluck('slug')->join(', ') . ")");
                $skippedCount++;
                continue;
            }

            if ($force && $hasRoles) {
                // Remove existing roles
                $user->roles()->detach();
                $this->line("  🔄 Removed existing roles from {$user->email}");
            }

            // Assign default role
            $user->roles()->attach($defaultRole);
            $this->info("  ✓ Assigned '{$defaultRoleSlug}' role to {$user->email} ({$user->name})");
            $assignedCount++;
        }

        $this->newLine();
        $this->info("✓ Completed!");
        $this->line("  - Assigned roles to {$assignedCount} user(s)");
        if ($skippedCount > 0) {
            $this->line("  - Skipped {$skippedCount} user(s) (already have roles)");
            $this->line("  - Use --force to reassign roles");
        }

        return 0;
    }
}
