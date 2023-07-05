<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->create([
                'name' => 'Keigo Victor Fujita',
                'email' => 'keigovictor.fujita@concentrix.com',
                'password' => Hash::make('password')
            ]);

        User::query()
            ->create([
                'name' => 'Boya Vivo',
                'email' => 'joseaugusto.vivo@concentrix.com',
                'password' => Hash::make('password')
            ]);

        User::query()
            ->create([
                'name' => 'Angelo Martinez',
                'email' => 'angelo.martinez@concentrix.com',
                'password' => Hash::make('password')
            ]);

        $users = User::all();

        //Sync super_admin role to users
        foreach ($users as $user){
            $user->syncRoles([1]);
        }

    }
}
