<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LaratrustDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('permission_role')->truncate();
        DB::table('role_user')->truncate();
        DB::table('permission_user')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $now = now();

        // Roles
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full access to all resources'],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Manage users and own data'],
            ['name' => 'user', 'display_name' => 'User', 'description' => 'Basic access to own data'],
        ];
        $roleIdByName = [];
        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'uuid' => DB::raw("COALESCE(uuid, '" . Str::uuid() . "')"),
                    'slug' => Str::slug($role['name']), // <-- ADDED: To match your database table
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'status' => '1', // <-- ADDED: '1' for active
                    'updated_at' => $now,
                    'created_at' => DB::raw("COALESCE(created_at, '$now')"),
                ]
            );
            $roleIdByName[$role['name']] = DB::table('roles')->where('name', $role['name'])->value('id');
        }

        // Permissions
        $permissions = [
            ['name' => 'users.read', 'display_name' => 'Read Users', 'description' => 'View users list and details'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create new users'],
            ['name' => 'users.update', 'display_name' => 'Update Users', 'description' => 'Edit existing users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Remove users'],

            ['name' => 'profile.read', 'display_name' => 'Read Profile', 'description' => 'View own profile'],
            ['name' => 'profile.update', 'display_name' => 'Update Profile', 'description' => 'Edit own profile'],

            ['name' => 'roles.read', 'display_name' => 'Read Roles', 'description' => 'View roles and permissions'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Create roles'],
            ['name' => 'roles.update', 'display_name' => 'Update Roles', 'description' => 'Edit roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Remove roles'],
        ];

        $permissionIdByName = [];
        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                [
                    'uuid' => DB::raw("COALESCE(uuid, '" . Str::uuid() . "')"),
                    'slug' => Str::slug($perm['name']),
                    'display_name' => $perm['display_name'],
                    'description' => $perm['description'],
                    'updated_at' => $now,
                    'created_at' => DB::raw("COALESCE(created_at, '$now')"),
                ]
            ); 
            $permissionIdByName[$perm['name']] = DB::table('permissions')->where('name', $perm['name'])->value('id');
        }

        // permission_role assignments
        $assignPermissions = function (array $permissionNames, int $roleId) use ($permissionIdByName) {
            foreach ($permissionNames as $name) {
                $permissionId = $permissionIdByName[$name] ?? null;
                if ($permissionId) {
                    DB::table('permission_role')->updateOrInsert(
                        ['permission_id' => $permissionId, 'role_id' => $roleId],
                        []
                    );
                }
            }
        };

        // Admin gets all permissions
        if (isset($roleIdByName['admin'])) {
            $assignPermissions(array_column($permissions, 'name'), $roleIdByName['admin']);
        }

        // Manager gets user management and profile permissions
        if (isset($roleIdByName['manager'])) {
            $managerPerms = [
                'users.read', 'users.create', 'users.update',
                'profile.read', 'profile.update',
            ];
            $assignPermissions($managerPerms, $roleIdByName['manager']);
        }

        // User gets only profile permissions
        if (isset($roleIdByName['user'])) {
            $userPerms = ['profile.read', 'profile.update'];
            $assignPermissions($userPerms, $roleIdByName['user']);
        }

        // Optionally assign admin role to first user if exists
        $firstUserId = DB::table('users')->orderBy('id')->value('id');
        if ($firstUserId && isset($roleIdByName['admin'])) {
            DB::table('role_user')->updateOrInsert(
                [
                    'role_id' => $roleIdByName['admin'],
                    'user_id' => $firstUserId,
                    'user_type' => 'App\\Models\\User',
                ],
                []
            );

            // Grant the user profile.update directly as an example
            $profileUpdateId = $permissionIdByName['profile.update'] ?? null;
            if ($profileUpdateId) {
                DB::table('permission_user')->updateOrInsert(
                    [
                        'permission_id' => $profileUpdateId,
                        'user_id' => $firstUserId,
                        'user_type' => 'App\\Models\\User',
                    ],
                    []
                );
            }
        }
    }
}