<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'add_users']);
        Permission::create(['name' => 'delete_users']);
        Permission::create(['name' => 'edit_users']);
        Permission::create(['name' => 'import_users']);
        Permission::create(['name' => 'export_users']);
        Permission::create(['name' => 'view_roles']);
        Permission::create(['name' => 'add_roles']);
        Permission::create(['name' => 'delete_roles']);
        Permission::create(['name' => 'edit_roles']);
        Permission::create(['name' => 'import_roles']);
        Permission::create(['name' => 'export_roles']);
        Permission::create(['name' => 'view_permissions']);
        Permission::create(['name' => 'export_permissions']);
        Permission::create(['name' => 'view_logs']);
        Permission::create(['name' => 'export_logs']);
    }
}
