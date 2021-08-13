<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Generator as Faker;


class UserSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public static function user()
    {
        $users = [
            [
                'id' => 1,
                'firstname'     => 'test1',
                'lastname'     => 'user1',
                'email'    => 'testuser1@yopmail.com',
                'phone_number' => '08000000000',
                'email_verified_at' => now(),
                'password' => '$2a$12$VRbWZuITjZGkAj6I1y3wk.YgTqf9HVeMtZVSUHc61K6PmiK38oCq.', // password
                'remember_token' => Str::random(10),
            ],
            [
                'id' => 2,
                'firstname'     => 'test2',
                'lastname'     => 'user2',
                'email'    => 'testuser2@yopmail.com',
                'phone_number' => '08000000001',
                'email_verified_at' => now(),
                'password' => '$2a$12$VRbWZuITjZGkAj6I1y3wk.YgTqf9HVeMtZVSUHc61K6PmiK38oCq.', // password
                'remember_token' => Str::random(10),
            ],
            [
                'id' => 3,
                'firstname'     => 'test3',
                'lastname'     => 'user3',
                'email'    => 'testuser3@yopmail.com',
                'phone_number' => '08000000002',
                'email_verified_at' => now(),
                'password' => '$2a$12$VRbWZuITjZGkAj6I1y3wk.YgTqf9HVeMtZVSUHc61K6PmiK38oCq.', // password
                'remember_token' => Str::random(10),
            ]
        ];

        /*
         * Add Users to the database
         *
         */
        foreach ($users as $user) {
            $newUser = User::where('email', $user['email'])->first();
            if ($newUser === null) {
                $newUser = User::create([
                    'firstname'          => $user['firstname'],
                    'lastname'          => $user['lastname'],
                    'email'          => $user['email'],
                    'phone_number'          => $user['phone_number'],
                    'email_verified_at'          => $user['email_verified_at'],
                    'password'          => $user['password'],
                    'remember_token'          => $user['remember_token'],
                ]);
                if($newUser){
                    Wallet::create([
                        'user_id' => $user['id'],
                        'code' => Str::random(10),
                        'balance' => 0,
                    ]);
                }
            }
        }
    }
}
