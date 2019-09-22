<?php

use Illuminate\Database\Seeder;
use database\factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        factory(App\User::class, 10)->create();
    }
}
