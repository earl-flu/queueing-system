<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
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
        $now = Carbon::now();
        User::create([
            'name' => 'Earl',
            'email' => 'earl@dev.com',
            'password' => Hash::make('testpassword'),
            'created_at' => $now, 'updated_at' => $now
        ]);
    }
}
