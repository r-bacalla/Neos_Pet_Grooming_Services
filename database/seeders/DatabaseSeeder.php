<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'role' => 'admin',
                'password' => Hash::make('Test1234!'),
            ]
        );

        $accounts = [
            ['name' => 'Demo Customer', 'email' => 'customer@example.com', 'role' => 'customer', 'password' => 'Customer123!'],
            ['name' => 'Demo Manager', 'email' => 'manager@example.com', 'role' => 'manager', 'password' => 'Manager123!'],
            ['name' => 'Demo Front Desk', 'email' => 'frontdesk@example.com', 'role' => 'frontdesk', 'password' => 'Frontdesk123!'],
            ['name' => 'Demo Owner', 'email' => 'owner@example.com', 'role' => 'owner', 'password' => 'Owner123!'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => Hash::make($account['password']),
                ]
            );
        }
    }
}
