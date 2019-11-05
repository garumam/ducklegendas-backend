<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@duck.com',
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'password' => bcrypt('123456'), // password
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'name' => 'moderador',
            'email' => 'moderador@duck.com',
            'user_type' => 'moderador',
            'email_verified_at' => now(),
            'password' => bcrypt('123456'), // password
            'remember_token' => Str::random(10),
        ]); 
        User::create([
            'name' => 'autor',
            'email' => 'autor@duck.com',
            'user_type' => 'autor',
            'email_verified_at' => now(),
            'password' => bcrypt('123456'), // password
            'remember_token' => Str::random(10),
        ]); 
        User::create([
            'name' => 'legender',
            'email' => 'legender@duck.com',
            'user_type' => 'legender',
            'email_verified_at' => now(),
            'password' => bcrypt('123456'), // password
            'remember_token' => Str::random(10),
        ]);
    }
}
