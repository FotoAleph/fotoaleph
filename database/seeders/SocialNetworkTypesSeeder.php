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
            'icon' => 'fab fa-facebook',
            'base_url' => 'https://facebook.com/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'Twitter',
            'icon' => 'fab fa-twitter',
            'base_url' => 'https://twitter.com/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'Instagram',
            'icon' => 'fab fa-instagram',
            'base_url' => 'https://instagram.com/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'LinkedIn',
            'icon' => 'fab fa-linkedin',
            'base_url' => 'https://linkedin.com/in/',
        ]);

        \App\Models\SocialNetworkType::create([
            'name' => 'YouTube',
            'icon' => 'fab fa-youtube',
            'base_url' => 'https://youtube.com/',
        ]);
    }
}
