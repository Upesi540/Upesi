<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'first_name' => 'Kpatra',
                'last_name' => 'Silah',
                'role' => 'Fondateur & CEO',
                'bio' => '<p>Ingénieur agronome de formation, Jean a passé 15 ans à travailler avec des coopératives agricoles en Afrique de l\'Ouest avant de fonder cette plateforme.</p>
<p>Sa vision : utiliser la technologie pour créer un marché agricole plus équitable et transparent. Passionné par l\'innovation sociale, il a déjà aidé plus de 10 000 producteurs à améliorer leurs revenus.</p>',
                'email' => 'jean.kouame@example.com',
                'phone' => '+225 07 89 00 11',
                'linkedin_url' => 'https://linkedin.com/in/jeankouame',
                'twitter_url' => 'https://twitter.com/jeankouame',
                'photo_path' => 'images/team/jean-kouame.jpg',
                'sort_order' => 10,
            ],
            [
                'first_name' => 'Aminata',
                'last_name' => 'Diallo',
                'role' => 'Directrice Technique (CTO)',
                'bio' => '<p>Aminata est une experte en développement de plateformes digitales avec 10 ans d\'expérience dans la tech. Elle a travaillé pour plusieurs startups innovantes avant de rejoindre l\'aventure.</p>
<p>Passionnée par l\'utilisation de la technologie pour résoudre des problèmes concrets, elle supervise le développement de toutes nos fonctionnalités et s\'assure que la plateforme reste accessible même dans les zones à faible connectivité.</p>',
                'email' => 'aminata.diallo@example.com',
                'phone' => '+225 05 67 89 01',
                'linkedin_url' => 'https://linkedin.com/in/aminatadiallo',
                'twitter_url' => 'https://twitter.com/aminatadiallo',
                'photo_path' => 'images/team/aminata-diallo.jpg',
                'sort_order' => 20,
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($members as $member) {
            $fullName = $member['first_name'] . ' ' . $member['last_name'];
            $exists = TeamMember::where('first_name', $member['first_name'])
                ->where('last_name', $member['last_name'])
                ->exists();

            if (!$exists) {
                TeamMember::create([
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                    'role' => $member['role'],
                    'bio' => $member['bio'],
                    'email' => $member['email'],
                    'phone' => $member['phone'],
                    'linkedin_url' => $member['linkedin_url'],
                    'twitter_url' => $member['twitter_url'],
                    'facebook_url' => $member['facebook_url'] ?? null,
                    'photo_path' => $member['photo_path'],
                    'sort_order' => $member['sort_order'],
                    'is_active' => true,
                ]);
                $created++;
                $this->command->info("✓ Membre créé: {$fullName}");
            } else {
                $skipped++;
                $this->command->warn("→ Membre ignoré (existant): {$fullName}");
            }
        }

        $this->command->info("✅ {$created} membres créés, {$skipped} membres existants conservés");
    }
}
