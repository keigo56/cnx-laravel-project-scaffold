<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::query()
            ->create([
                'workday_id' => '101794197',
                'name' => 'Keigo Victor Fujita',
                'FirstName' => 'Keigo Victor',
                'LastName' => 'Fujita',
                'MiddleName' => 'Templo',
                'EmailAddress' => 'keigovictor.fujita@concentrix.com',
                'Position' => 'Analyst, Delivery Excellence',
            ]);

        Employee::query()
            ->create([
                'workday_id' => '1018005',
                'name' => 'Jose Augusto Vivo',
                'FirstName' => 'Jose Augusto',
                'LastName' => 'Vivo',
                'MiddleName' => 'Teruel',
                'EmailAddress' => 'joseaugusto.vivo@concentrix.com',
                'Position' => 'Analyst, Delivery Excellence',
            ]);

        Employee::query()
            ->create([
                'workday_id' => '574338',
                'name' => 'Angelo Martinez',
                'FirstName' => 'Angelo',
                'LastName' => 'Martinez',
                'MiddleName' => 'Briones',
                'EmailAddress' => 'angelo.martinez@concentrix.com',
                'Position' => 'Sr. Analyst, Delivery Excellence',
            ]);
    }
}
