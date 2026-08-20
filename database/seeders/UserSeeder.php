<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = '00000000';

        $dummyUsers = [
            [
                'name' => 'admin',
                'email' => 'admin@checkpoint.store',
                'password' => $password,
                'role' => 'admin',
            ],
            [
                'name' => 'customer_1',
                'email' => 'customer1@checkpoint.store',
                'password' => $password,
                'role' => 'customer',
            ],
            [
                'name' => 'customer_2',
                'email' => 'customer2@checkpoint.store',
                'password' => $password,
                'role' => 'customer',
            ],
        ];

        foreach ($dummyUsers as $newUser) {
            User::create($newUser);
        }
    }
}
