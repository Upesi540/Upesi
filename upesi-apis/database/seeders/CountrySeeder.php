<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class CountrySeeder extends Seeder
{
    /**
     * Table de correspondance des noms de pays en français
     */
    private array $frenchNames = [
        'AF' => 'Afghanistan',
        'ZA' => 'Afrique du Sud',
        'AL' => 'Albanie',
        'DZ' => 'Algérie',
        'DE' => 'Allemagne',
        'AD' => 'Andorre',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctique',
        'AG' => 'Antigua-et-Barbuda',
        'SA' => 'Arabie saoudite',
        'AR' => 'Argentine',
        'AM' => 'Arménie',
        'AW' => 'Aruba',
        'AU' => 'Australie',
        'AT' => 'Autriche',
        'AZ' => 'Azerbaïdjan',
        'BS' => 'Bahamas',
        'BH' => 'Bahreïn',
        'BD' => 'Bangladesh',
        'BB' => 'Barbade',
        'BE' => 'Belgique',
        'BZ' => 'Belize',
        'BJ' => 'Bénin',
        'BM' => 'Bermudes',
        'BT' => 'Bhoutan',
        'BY' => 'Biélorussie',
        'BO' => 'Bolivie',
        'BQ' => 'Pays-Bas caribéens',
        'BA' => 'Bosnie-Herzégovine',
        'BW' => 'Botswana',
        'BR' => 'Brésil',
        'BN' => 'Brunéi Darussalam',
        'BG' => 'Bulgarie',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodge',
        'CM' => 'Cameroun',
        'CA' => 'Canada',
        'CV' => 'Cap-Vert',
        'CL' => 'Chili',
        'CN' => 'Chine',
        'CX' => 'Île Christmas',
        'CY' => 'Chypre',
        'CC' => 'Îles Cocos',
        'CO' => 'Colombie',
        'KM' => 'Comores',
        'CG' => 'Congo-Brazzaville',
        'CD' => 'Congo-Kinshasa',
        'CK' => 'Îles Cook',
        'KP' => 'Corée du Nord',
        'KR' => 'Corée du Sud',
        'CR' => 'Costa Rica',
        'CI' => 'Côte d\'Ivoire',
        'HR' => 'Croatie',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'DK' => 'Danemark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominique',
        'EG' => 'Égypte',
        'SV' => 'Salvador',
        'AE' => 'Émirats arabes unis',
        'EC' => 'Équateur',
        'ER' => 'Érythrée',
        'ES' => 'Espagne',
        'EE' => 'Estonie',
        'SZ' => 'Eswatini',
        'US' => 'États-Unis',
        'ET' => 'Éthiopie',
        'FK' => 'Îles Malouines',
        'FO' => 'Îles Féroé',
        'FJ' => 'Fidji',
        'FI' => 'Finlande',
        'FR' => 'France',
        'GA' => 'Gabon',
        'GM' => 'Gambie',
        'GE' => 'Géorgie',
        'GS' => 'Géorgie du Sud-et-les Îles Sandwich du Sud',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Grèce',
        'GD' => 'Grenade',
        'GL' => 'Groenland',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernesey',
        'GN' => 'Guinée',
        'GQ' => 'Guinée équatoriale',
        'GW' => 'Guinée-Bissau',
        'GY' => 'Guyana',
        'GF' => 'Guyane française',
        'HT' => 'Haïti',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hongrie',
        'IM' => 'Île de Man',
        'UM' => 'Îles mineures éloignées des États-Unis',
        'VG' => 'Îles Vierges britanniques',
        'VI' => 'Îles Vierges des États-Unis',
        'IN' => 'Inde',
        'ID' => 'Indonésie',
        'IR' => 'Iran',
        'IQ' => 'Irak',
        'IE' => 'Irlande',
        'IS' => 'Islande',
        'IL' => 'Israël',
        'IT' => 'Italie',
        'JM' => 'Jamaïque',
        'JP' => 'Japon',
        'JE' => 'Jersey',
        'JO' => 'Jordanie',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KG' => 'Kirghizistan',
        'KI' => 'Kiribati',
        'KW' => 'Koweït',
        'RE' => 'La Réunion',
        'LA' => 'Laos',
        'LS' => 'Lesotho',
        'LV' => 'Lettonie',
        'LB' => 'Liban',
        'LR' => 'Libéria',
        'LY' => 'Libye',
        'LI' => 'Liechtenstein',
        'LT' => 'Lituanie',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macédoine du Nord',
        'MG' => 'Madagascar',
        'MY' => 'Malaisie',
        'MW' => 'Malawi',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malte',
        'MP' => 'Îles Mariannes du Nord',
        'MA' => 'Maroc',
        'MH' => 'Îles Marshall',
        'MQ' => 'Martinique',
        'MU' => 'Maurice',
        'MR' => 'Mauritanie',
        'YT' => 'Mayotte',
        'MX' => 'Mexique',
        'FM' => 'Micronésie',
        'MD' => 'Moldavie',
        'MC' => 'Monaco',
        'MN' => 'Mongolie',
        'ME' => 'Monténégro',
        'MS' => 'Montserrat',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibie',
        'NR' => 'Nauru',
        'NP' => 'Népal',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigéria',
        'NU' => 'Niue',
        'NF' => 'Île Norfolk',
        'NO' => 'Norvège',
        'NC' => 'Nouvelle-Calédonie',
        'NZ' => 'Nouvelle-Zélande',
        'IO' => 'Territoire britannique de l\'océan Indien',
        'OM' => 'Oman',
        'UG' => 'Ouganda',
        'UZ' => 'Ouzbékistan',
        'PK' => 'Pakistan',
        'PW' => 'Palaos',
        'PS' => 'Palestine',
        'PA' => 'Panama',
        'PG' => 'Papouasie-Nouvelle-Guinée',
        'PY' => 'Paraguay',
        'NL' => 'Pays-Bas',
        'PE' => 'Pérou',
        'PH' => 'Philippines',
        'PN' => 'Îles Pitcairn',
        'PL' => 'Pologne',
        'PF' => 'Polynésie française',
        'PR' => 'Porto Rico',
        'PT' => 'Portugal',
        'QA' => 'Qatar',
        'DO' => 'République dominicaine',
        'CZ' => 'République tchèque',
        'RO' => 'Roumanie',
        'GB' => 'Royaume-Uni',
        'RU' => 'Russie',
        'RW' => 'Rwanda',
        'EH' => 'Sahara occidental',
        'BL' => 'Saint-Barthélemy',
        'KN' => 'Saint-Christophe-et-Niévès',
        'SM' => 'Saint-Marin',
        'MF' => 'Saint-Martin',
        'SX' => 'Saint-Martin (partie néerlandaise)',
        'PM' => 'Saint-Pierre-et-Miquelon',
        'VC' => 'Saint-Vincent-et-les-Grenadines',
        'SH' => 'Sainte-Hélène',
        'LC' => 'Sainte-Lucie',
        'SB' => 'Îles Salomon',
        'WS' => 'Samoa',
        'AS' => 'Samoa américaines',
        'ST' => 'Sao Tomé-et-Principe',
        'SN' => 'Sénégal',
        'RS' => 'Serbie',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapour',
        'SK' => 'Slovaquie',
        'SI' => 'Slovénie',
        'SO' => 'Somalie',
        'SD' => 'Soudan',
        'SS' => 'Soudan du Sud',
        'LK' => 'Sri Lanka',
        'SE' => 'Suède',
        'CH' => 'Suisse',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard et Jan Mayen',
        'SY' => 'Syrie',
        'TJ' => 'Tadjikistan',
        'TW' => 'Taïwan',
        'TZ' => 'Tanzanie',
        'TD' => 'Tchad',
        'TF' => 'Terres australes françaises',
        'TH' => 'Thaïlande',
        'TL' => 'Timor oriental',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinité-et-Tobago',
        'TN' => 'Tunisie',
        'TM' => 'Turkménistan',
        'TC' => 'Îles Turques-et-Caïques',
        'TR' => 'Turquie',
        'TV' => 'Tuvalu',
        'UA' => 'Ukraine',
        'UY' => 'Uruguay',
        'VU' => 'Vanuatu',
        'VA' => 'Vatican',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'WF' => 'Wallis-et-Futuna',
        'YE' => 'Yémen',
        'ZM' => 'Zambie',
        'ZW' => 'Zimbabwe',
        'AX' => 'Îles Åland',
        'BV' => 'Île Bouvet',
        'HM' => 'Îles Heard-et-MacDonald',
        'KY' => 'Îles Caïmans',
        'FK' => 'Îles Malouines',
        'FO' => 'Îles Féroé',
        'MP' => 'Îles Mariannes du Nord',
        'MH' => 'Îles Marshall',
        'SB' => 'Îles Salomon',
        'VG' => 'Îles Vierges britanniques',
        'VI' => 'Îles Vierges des États-Unis',
        'UM' => 'Îles mineures éloignées des États-Unis',
    ];
    public function run(): void
    {
        $apiKey = env('CSC_API_KEY');

        if (!$apiKey) {
            $this->command->error('❌ CSC_API_KEY not set in .env');
            return;
        }

        $this->command->info('🌍 Importation des pays...');
        $this->command->getOutput()->progressStart(3);

        // ========== 1️⃣ PAYS ==========
        $this->importCountries($apiKey);

        // ========== 2️⃣ ÉTATS ==========
        $this->command->getOutput()->progressAdvance();
        $this->command->line(" 🏙️ Importation des états/régions...");
        $this->importStates($apiKey);

        // ========== 3️⃣ VILLES ==========
        $this->command->getOutput()->progressAdvance();
        $this->command->line(" 🏘️ Importation des villes...");
        $this->importCities($apiKey);

        $this->command->getOutput()->progressFinish();

        // ========== RÉSULTAT ==========
        $this->showResults();
    }

    private function importCountries($apiKey)
    {
        $response = Http::withHeaders(['X-CSCAPI-KEY' => $apiKey])
            ->timeout(120)
            ->get('https://api.countrystatecity.in/v1/countries');

        if ($response->failed()) {
            $this->command->error('❌ Failed to fetch countries.');
            return;
        }

        $countries = $response->json();
        $this->command->getOutput()->progressAdvance();
        $this->command->info(" ✅ " . count($countries) . " pays récupérés");

        $imported = 0;
        foreach ($countries as $data) {
            $frenchName = $this->frenchNames[$data['iso2']] ?? $data['name'];

            Country::updateOrCreate(
                ['iso2' => $data['iso2']],
                [
                    'name' => $frenchName,
                    'iso3' => $data['iso3'],
                    'phone_code' => $data['phonecode'],
                    'capital' => $data['capital'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'native' => $data['native'] ?? null,
                    'emoji' => $data['emoji'] ?? null,
                    'emojiU' => $data['emojiU'] ?? null,
                ]
            );
            $imported++;
        }

        $this->command->line("   ✅ {$imported} pays importés (en français)");
    }

    private function importStates($apiKey)
    {
        $countries = Country::all();
        $totalStates = 0;
        $bar = $this->command->getOutput()->createProgressBar($countries->count());
        $bar->start();

        foreach ($countries as $country) {
            $response = Http::withHeaders(['X-CSCAPI-KEY' => $apiKey])
                ->timeout(60)
                ->get("https://api.countrystatecity.in/v1/countries/{$country->iso2}/states");

            if ($response->successful()) {
                $states = $response->json();
                $totalStates += count($states);

                foreach ($states as $stateData) {
                    State::updateOrCreate(
                        [
                            'iso2' => $stateData['iso2'] ?? null,
                            'country_id' => $country->id
                        ],
                        ['name' => $stateData['name']]
                    );
                }
            }

            $bar->advance();
            usleep(100000); // Pause de 0.1s pour éviter de surcharger l'API
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->line("   ✅ {$totalStates} états importés");
    }

    private function importCities($apiKey)
    {
        $states = State::all();
        $totalCities = 0;
        $bar = $this->command->getOutput()->createProgressBar($states->count());
        $bar->start();

        foreach ($states as $state) {
            $country = Country::find($state->country_id);
            if (!$country) continue;

            $response = Http::withHeaders(['X-CSCAPI-KEY' => $apiKey])
                ->timeout(60)
                ->get("https://api.countrystatecity.in/v1/countries/{$country->iso2}/states/{$state->iso2}/cities");

            if ($response->successful()) {
                $cities = $response->json();
                $totalCities += count($cities);

                foreach ($cities as $cityData) {
                    City::firstOrCreate(
                        [
                            'name' => $cityData['name'],
                            'state_id' => $state->id
                        ]
                    );
                }
            }

            $bar->advance();
            usleep(100000); // Pause de 0.1s
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->line("   ✅ {$totalCities} villes importées");
    }

    private function showResults()
    {
        $this->command->newLine(2);
        $this->command->info('🎉 ' . str_repeat('=', 50));
        $this->command->info('🎉 IMPORTATION TERMINÉE AVEC SUCCÈS !');
        $this->command->info('🎉 ' . str_repeat('=', 50));
        $this->command->newLine();

        $this->command->line("   🌍 Pays: \033[32m" . Country::count() . "\033[0m importés");
        $this->command->line("   🏙️ États: \033[32m" . State::count() . "\033[0m importés");
        $this->command->line("   🏘️ Villes: \033[32m" . City::count() . "\033[0m importées");
        $this->command->newLine();

        $this->command->line("📋 Exemples de pays :");
        Country::limit(5)->get()->each(function ($country) {
            $this->command->line("   - {$country->emoji} \033[33m{$country->name}\033[0m");
        });
    }
}
