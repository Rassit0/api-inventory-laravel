<?php

namespace Database\Seeders;

use App\Models\Config\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 10 sucursales de ejemplo
        Branch::factory()->count(1)->create();
    }
}
