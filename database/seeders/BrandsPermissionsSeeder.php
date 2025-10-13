<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class BrandsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find Super Administrator role
        $superAdminRole = Role::where('role_name', 'Super Administrator')->first();

        if (!$superAdminRole) {
            $this->command->error('Super Administrator role not found!');
            return;
        }

        $this->command->info('Found Super Administrator role (ID: ' . $superAdminRole->id . ')');

        // Define brands permissions
        $brandsPermissions = [
            'brands' => ['brands', 'brands.create', 'brands.edit', 'brands.delete', 'brands.view']
        ];

        $addedCount = 0;

        foreach ($brandsPermissions as $permission => $routes) {
            foreach ($routes as $route) {
                // Check if permission already exists
                $existingPermission = Permission::where('role_id', $superAdminRole->id)
                    ->where('permission', $permission)
                    ->where('routes', $route)
                    ->first();

                if (!$existingPermission) {
                    // Create new permission
                    Permission::create([
                        'role_id' => $superAdminRole->id,
                        'permission' => $permission,
                        'routes' => $route
                    ]);

                    $this->command->info('Added permission: ' . $permission . ' -> ' . $route);
                    $addedCount++;
                } else {
                    $this->command->line('Permission already exists: ' . $permission . ' -> ' . $route);
                }
            }
        }

        $this->command->info('Successfully added ' . $addedCount . ' brands permissions to Super Administrator role!');
    }
}
