<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = Permission::all()->pluck('id');

        $super_admin = Role::create([
            'name' => 'super_admin',
        ]);

        $admin = Role::create([
            'name' => 'admin',
        ]);

        $super_admin->syncPermissions($permissions);
        $admin->syncPermissions($permissions);
    }
}
