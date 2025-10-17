<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $permissions = [
            // User permissions
            ['name' => 'Read Users', 'slug' => 'users:read', 'resource' => 'users', 'action' => 'read'],
            ['name' => 'Create Users', 'slug' => 'users:create', 'resource' => 'users', 'action' => 'create'],
            ['name' => 'Update Users', 'slug' => 'users:update', 'resource' => 'users', 'action' => 'update'],
            ['name' => 'Delete Users', 'slug' => 'users:delete', 'resource' => 'users', 'action' => 'delete'],
            
            // Profile permissions
            ['name' => 'Read Profile', 'slug' => 'profile:read', 'resource' => 'profile', 'action' => 'read'],
            ['name' => 'Update Profile', 'slug' => 'profile:update', 'resource' => 'profile', 'action' => 'update'],
            
            // Role permissions
            ['name' => 'Read Roles', 'slug' => 'roles:read', 'resource' => 'roles', 'action' => 'read'],
            ['name' => 'Create Roles', 'slug' => 'roles:create', 'resource' => 'roles', 'action' => 'create'],
            ['name' => 'Update Roles', 'slug' => 'roles:update', 'resource' => 'roles', 'action' => 'update'],
            ['name' => 'Delete Roles', 'slug' => 'roles:delete', 'resource' => 'roles', 'action' => 'delete'],
            
            // Permission permissions
            ['name' => 'Read Permissions', 'slug' => 'permissions:read', 'resource' => 'permissions', 'action' => 'read'],
            ['name' => 'Create Permissions', 'slug' => 'permissions:create', 'resource' => 'permissions', 'action' => 'create'],
            ['name' => 'Update Permissions', 'slug' => 'permissions:update', 'resource' => 'permissions', 'action' => 'update'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions:delete', 'resource' => 'permissions', 'action' => 'delete'],
            
            // Admin permissions
            ['name' => 'Admin Access', 'slug' => 'admin:access', 'resource' => 'admin', 'action' => 'access'],
            ['name' => 'System Settings', 'slug' => 'system:settings', 'resource' => 'system', 'action' => 'settings'],
        ];
       
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'level' => 100,
                'is_active' => true
            ]
        );

        $moderatorRole = Role::firstOrCreate(
            ['slug' => 'BDM'],
            [
                'name' => 'BDM',
                'description' => 'Limited admin access for content moderation',
                'level' => 50,
                'is_active' => true
            ]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Basic user access',
                'level' => 1,
                'is_active' => true
            ]
        );

        // Assign permissions to roles
        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all()->pluck('id')->toArray());

        // Moderator gets user management and profile permissions
        $moderatorPermissions = Permission::whereIn('resource', ['users', 'profile'])
            ->orWhere('slug', 'admin:access')
            ->pluck('id')
            ->toArray();
        $moderatorRole->syncPermissions($moderatorPermissions);

        // User gets only profile permissions
        $userPermissions = Permission::where('resource', 'profile')
            ->pluck('id')
            ->toArray();
        $userRole->syncPermissions($userPermissions);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}