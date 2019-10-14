<?php

use Illuminate\Database\Seeder;
use App\Subtitle;
use App\Category;

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
        factory(App\User::class, 250)->create();

        $category = Category::create([
            'name' => 'Comédia'
        ]);

        Subtitle::create([
            'name' => 'Serie da google',
            'year' => 2019,
            'url' => 'https://www.google.com/',
            'image' => '',
            'status' => 'CONCLUÍDA',
            'author' => 1,
            'category' => $category->id
        ]);
 
    }
}
