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
        //factory(App\User::class, 250)->create();

        $categories = array(
            'Ação',
            'Animação',
            'Aventura',
            'Biografia',
            'Comédia',
            'Crime',
            'Documentário',
            'Drama',
            'Esporte',
            'Família',
            'Fantasia',
            'Faroeste',
            'Ficção',
            'Guerra',
            'História',
            'Mistério',
            'Musical',
            'Policial',
            'Romance',
            'Suspense',
            'Terror'
        );
        foreach($categories as $category){
            Category::create([
                'name' => $category
            ]);
        }
        
        // Subtitle::create([
        //     'name' => 'Stranger things',
        //     'year' => 2019,
        //     'url' => 'https://www.google.com/',
        //     'episode' => 's03e03',
        //     'type' => 'SERIE',
        //     'image' => '',
        //     'status' => 'APROVADA',
        //     'author' => 1,
        //     'category' => $category->id
        // ]);

        // factory(App\Subtitle::class, 100)->create();

        // SubtitleProgress::create([
        //     'name' => 'Serie em andamento',
        //     'percent' => 80,
        //     'author' => 1
        // ]);
            
        // Gallery::create([
        //     'name' => 'peaky_blinders',
        //     'tags' => 'ciganos gangue mafia peaky blinders',
        //     'image' => 'img/subtitles/1.jpg'
        // ]);

        // Gallery::create([
        //     'name' => 'stranger_things',
        //     'tags' => 'ficção científica terror stranger things',
        //     'image' => 'img/subtitles/2.jpg'
        // ]);
    }
}
