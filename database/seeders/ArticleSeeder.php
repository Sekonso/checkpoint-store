<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Article;
use App\Models\ArticleTag;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'article_seeder',
            'email' => 'article_seeder@checkpoint.store',
            'password' => '00000000',
            'role' => 'admin',
        ]);

        $tags = [
            'Tips',
            'Guides',
            'Trivia',
            'How To',
            'Recommendations',
            'Reviews',
            'Setup',
            'Gaming',
        ];

        $tagModels = collect($tags)->mapWithKeys(function ($tag) {
            $model = ArticleTag::firstOrCreate([
                'name' => $tag,
            ]);

            return [$tag => $model];
        });

        $articles = [
            [
                'title' => 'The Ultimate Guide to Choosing a Gaming Mouse',
                'content' => 'Choosing the right gaming mouse can make a significant difference in your gaming experience. From lightweight designs for fast-paced FPS games to ergonomic mice with additional buttons for MMO players, finding the right balance between sensor performance, weight, grip style, and button layout is essential. Consider your favorite games and playstyle before choosing your next gaming mouse.',
                'tags' => [
                    'Guides',
                    'Tips',
                    'Recommendations',
                ],
                'status' => 'draft'
            ],
            [
                'title' => 'How to Choose the Right Gaming Keyboard',
                'content' => 'A good gaming keyboard should provide responsive input, comfortable key switches, and a layout that fits your gaming setup. Mechanical keyboards are popular among gamers because of their tactile feedback and durability. Whether you prefer linear, tactile, or clicky switches, choosing the right keyboard can improve both your gaming performance and everyday typing experience.',
                'tags' => [
                    'How To',
                    'Tips',
                    'Recommendations',
                ],
                'status' => 'archived'
            ],
            [
                'title' => 'Gaming Headset Buying Guide',
                'content' => 'Audio plays an important role in competitive and immersive gaming. A quality gaming headset can help you hear important details such as footsteps, reloads, and environmental sounds while also providing clear voice communication with teammates. When shopping for a headset, consider sound quality, microphone performance, comfort, connectivity, and battery life for wireless models.',
                'tags' => [
                    'Guides',
                    'Reviews',
                    'Recommendations',
                ],
                'status' => 'published'
            ],
            [
                'title' => 'Build the Perfect Gaming Setup',
                'content' => 'Creating a great gaming setup is about more than having a powerful PC or console. The right combination of a gaming monitor, keyboard, mouse, headset, desk, and chair can make your gaming sessions more comfortable and enjoyable. Start with the peripherals that matter most to your games, then gradually upgrade the rest of your setup as your needs evolve.',
                'tags' => [
                    'Setup',
                    'Tips',
                    'Gaming',
                ],
                'status' => 'published'
            ],
            [
                'title' => 'Gaming Accessories Worth Upgrading',
                'content' => 'Small upgrades can make a surprisingly big difference to your gaming setup. A large mouse pad can provide more room for low-sensitivity mouse movements, a quality controller can improve comfort during long sessions, and a proper headset stand can help keep your desk organized. Focus on accessories that solve problems in your current setup rather than simply adding more equipment.',
                'tags' => [
                    'Tips',
                    'Recommendations',
                    'Gaming',
                ],
                'status' => 'published'
            ],
        ];

        foreach ($articles as $articleData) {
            $article = Article::create([
                'user_id' => $user->id,
                'title' => $articleData['title'],
                'featured_image' => 'sample_article.webp',
                'content' => $articleData['content'],
                'status' => $articleData['status']
            ]);

            $article->tags()->attach(
                collect($articleData['tags'])
                    ->map(fn($tag) => $tagModels[$tag]->id)
                    ->toArray()
            );
        }

        Article::factory()->count(100)->create();
    }
}