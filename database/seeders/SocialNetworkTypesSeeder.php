<?php

namespace Database\Seeders;

use App\Models\SocialNetworkType;
use Illuminate\Database\Seeder;

class SocialNetworkTypesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->types() as $type) {
            $values = ['base_url' => $type['base_url']];

            if (array_key_exists('icon', $type)) {
                $values['icon'] = $type['icon'];
            }

            SocialNetworkType::updateOrCreate(
                ['name' => $type['name']],
                $values,
            );
        }
    }

    /**
     * @return array<int, array{name: string, base_url: string, icon?: string|null}>
     */
    private function types(): array
    {
        return [
            ['name' => 'Facebook', 'base_url' => 'https://facebook.com/', 'icon' => 'facebook.svg'],
            ['name' => 'Twitter', 'base_url' => 'https://twitter.com/', 'icon' => 'x-twitter.svg'],
            ['name' => 'X / Twitter', 'base_url' => 'https://x.com/', 'icon' => 'x-twitter.svg'],
            ['name' => 'Instagram', 'base_url' => 'https://instagram.com/', 'icon' => 'instagram.svg'],
            ['name' => 'LinkedIn', 'base_url' => 'https://linkedin.com/in/', 'icon' => 'linkedin.svg'],
            ['name' => 'YouTube', 'base_url' => 'https://youtube.com/', 'icon' => 'youtube.svg'],
            ['name' => 'TikTok', 'base_url' => 'https://tiktok.com/@', 'icon' => 'tiktok.svg'],
            ['name' => 'GitHub', 'base_url' => 'https://github.com/', 'icon' => 'github.svg'],
            ['name' => 'Google Maps', 'base_url' => 'https://maps.app.goo.gl/', 'icon' => 'google-maps.svg'],
            ['name' => 'Discord', 'base_url' => 'https://discord.com/', 'icon' => 'discord.svg'],
            ['name' => 'Reddit', 'base_url' => 'https://www.reddit.com/user/', 'icon' => 'reddit.svg'],
            ['name' => 'Figma', 'base_url' => 'https://www.figma.com/@', 'icon' => 'figma.svg'],
            ['name' => 'Canva', 'base_url' => 'https://www.canva.com/brand/', 'icon' => 'canva.svg'],
            ['name' => 'Chess.com', 'base_url' => 'https://www.chess.com/member/', 'icon' => 'chess.svg'],
        ];
    }
}
