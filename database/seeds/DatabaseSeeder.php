<?php

use Illuminate\Database\Seeder;
use App\Subtitle;
use App\Category;
use App\SubtitleProgress;
use App\Gallery;

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
            'status' => 'APROVADA',
            'author' => 1,
            'category' => $category->id
        ]);

        SubtitleProgress::create([
            'name' => 'Serie em andamento',
            'percent' => 80,
            'status' => 'EM ANDAMENTO',
            'author' => 1
        ]);
            
        Gallery::create([
            'name' => 'peaky_blinders',
            'tags' => 'ciganos gangue mafia peaky blinders',
            'image' => 'img/subtitles/1.jpg'
        ]);

        Gallery::create([
            'name' => 'stranger_things',
            'tags' => 'ficção científica terror stranger things',
            'image' => 'img/subtitles/2.jpg'
        ]);
    }
}
