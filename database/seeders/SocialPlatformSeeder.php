<?php

namespace Database\Seeders;

use App\Models\SocialPlatform;
use Illuminate\Database\Seeder;

class SocialPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            // Réseaux Sociaux Généraux
            ['name' => 'Facebook', 'slug' => 'facebook', 'icon' => 'Facebook', 'base_url' => 'https://facebook.com/'],
            ['name' => 'Instagram', 'slug' => 'instagram', 'icon' => 'Instagram', 'base_url' => 'https://instagram.com/'],
            ['name' => 'X (Twitter)', 'slug' => 'twitter', 'icon' => 'Twitter', 'base_url' => 'https://x.com/'],
            ['name' => 'TikTok', 'slug' => 'tiktok', 'icon' => 'Music2', 'base_url' => 'https://tiktok.com/@'],
            ['name' => 'LinkedIn', 'slug' => 'linkedin', 'icon' => 'Linkedin', 'base_url' => 'https://linkedin.com/in/'],
            ['name' => 'Snapchat', 'slug' => 'snapchat', 'icon' => 'Ghost', 'base_url' => 'https://snapchat.com/add/'],
            ['name' => 'Threads', 'slug' => 'threads', 'icon' => 'AtSign', 'base_url' => 'https://threads.net/@'],

            // Vidéo & Gaming (Utile pour les clubs qui streament ou font des tutos)
            ['name' => 'YouTube', 'slug' => 'youtube', 'icon' => 'Youtube', 'base_url' => 'https://youtube.com/'],
            ['name' => 'Twitch', 'slug' => 'twitch', 'icon' => 'Twitch', 'base_url' => 'https://twitch.tv/'],
            ['name' => 'Discord', 'slug' => 'discord', 'icon' => 'MessageCircle', 'base_url' => 'https://discord.gg/'],

            // Communication Directe
            ['name' => 'WhatsApp', 'slug' => 'whatsapp', 'icon' => 'Phone', 'base_url' => 'https://wa.me/'],
            ['name' => 'Telegram', 'slug' => 'telegram', 'icon' => 'Send', 'base_url' => 'https://t.me/'],

            // Sport & Tracking (Très pertinent pour une plateforme de clubs)
            ['name' => 'Strava', 'slug' => 'strava', 'icon' => 'Activity', 'base_url' => 'https://strava.com/athletes/'],

            // Web & Portfolio
            ['name' => 'Site Web', 'slug' => 'website', 'icon' => 'Globe', 'base_url' => ''],
        ];

        foreach ($platforms as $platform) {
            SocialPlatform::updateOrCreate(
                ['slug' => $platform['slug']],
                $platform
            );
        }
    }
}
