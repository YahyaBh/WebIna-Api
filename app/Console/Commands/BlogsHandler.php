<?php

namespace App\Console\Commands;

use App\Models\blogs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BlogsHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'http:blogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a request to get news from the web';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('http://api.mediastack.com/v1/news?access_key=eddbad880a36d275292da0d9185ef3b6&keywords=Digital Business');


        // Assuming the API response has a key named 'data' containing an array of news items.
        $newsItems = $response['data'] ?? [];

        // Save 10 news items to the database.
        $newsToSave = array_slice($newsItems, 0, 10);

        foreach ($newsToSave as $newsItem) {
            blogs::create([
                'name' => $newsItem['title'],
                'description' => $newsItem['description'],
                'link' => $newsItem['url'],
                'image' => $newsItem['image'] ?? env('APP_URL') . '/images/admins/home/blogs/Wall post-amico.svg',
            ]);
        }

        $this->info('command executed successfully.'); // Output information

        return 0; // Return an integer exit code.
    }
}
