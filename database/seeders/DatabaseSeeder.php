<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'id' => 1,
            'type' => 'comum',
            'balance' => 100.00
        ]);

        User::factory()->create([
            'id' => 2,
            'type' => 'comum',
            'balance' => 200.00
        ]);

        User::factory()->create([
            'id' => 3,
            'type' => 'comum',
            'balance' => 20.00
        ]);

        User::factory()->create([
            'id' => 4,
            'type' => 'lojista',
            'balance' => 100.00
        ]);

        User::factory()->create([
            'id' => 5,
            'type' => 'lojista',
            'balance' => 80.00
        ]);
    }
}
