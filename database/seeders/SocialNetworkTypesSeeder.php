<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SocialNetworkTypesSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\SocialNetworkType::create([
            'name' => 'Facebook',
            'base_url' => 'https://facebook.com/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'Twitter',
            'base_url' => 'https://twitter.com/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'Instagram',
            'base_url' => 'https://instagram.com/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'LinkedIn',
            'base_url' => 'https://linkedin.com/in/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'YouTube',
            'base_url' => 'https://youtube.com/',
        ]);
        \App\Models\SocialNetworkType::create([
            'name' => 'TikTok',
            'base_url' => 'https://tiktok.com/@',
        ]);
        \App\Models\SocialNetworkType::create([
            'name' => 'GitHub',
            'base_url' => 'https://github.com/'
        ]);
        
            \App\Models\SocialNetworkType::create([
                'name' => 'Discord',
                'base_url'  => 'https://discord.com/',
            ]);
         \App\Models\SocialNetworkType::create([
            'name' => 'Reddit',
            'base_url' => 'https://www.reddit.com/user/'
        ]);
         \App\Models\SocialNetworkType::create([
            'name' => 'Figma',
            'base_url' => 'https://www.figma.com/@'
        ]);
         \App\Models\SocialNetworkType::create([
            'name' => 'Canva',
            'base_url' => 'https://www.canva.com/brand/'
        ]);
         \App\Models\SocialNetworkType::create([
            'name' => 'Chess.com',
            'base_url' => 'https://www.chess.com/member/'
        ]);
    }
}
