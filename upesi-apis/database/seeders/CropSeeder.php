<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CropSeeder extends Seeder
{
    private $totalCrops = 0;

    public function run(): void
    {
        // Désactiver les événements
        Crop::unsetEventDispatcher();

        // Récupérer l'unité par défaut (kg)
        $kgUnit = Unit::where('symbol', 'kg')->first();
        if (!$kgUnit) {
            $kgUnit = Unit::create([
                'name' => 'Kilogramme',
                'symbol' => 'kg',
                'description' => 'Unité de poids standard',
                'is_active' => true
            ]);
        }

        $this->command->info('⏳ Création des cultures...');

        // Récupérer toutes les catégories par leur nom
        $categories = Category::all()->keyBy('name');

        // ========== 1. CÉRÉALES (Marché Matières Premières) ==========
        $this->seedCereales($categories, $kgUnit->id);

        // ========== 2. LÉGUMINEUSES (Marché Matières Premières) ==========
        $this->seedLegumineuses($categories, $kgUnit->id);

        // ========== 3. FRUITS (Marché Matières Premières) ==========
        $this->seedFruits($categories, $kgUnit->id);

        // ========== 4. LÉGUMES (Marché Matières Premières) ==========
        $this->seedLegumes($categories, $kgUnit->id);

        // ========== 5. LÉGUMES RACINES & TUBERCULES (Marché Matières Premières) ==========
        $this->seedLegumesRacines($categories, $kgUnit->id);

        // ========== 6. PRODUITS D'ÉLEVAGE (Marché Matières Premières) ==========
        $this->seedProduitsElevage($categories, $kgUnit->id);

        // ========== 7. PRODUITS D'EXPORTATION (Marché Matières Premières) ==========
        $this->seedProduitsExport($categories, $kgUnit->id);

        // ========== 8. SEMENCES (Marché Intrants) ==========
        $this->seedSemences($categories, $kgUnit->id);

        // ========== 9. INTRAINTS ÉLEVAGE (Marché Intrants) ==========
        $this->seedIntrantsElevage($categories, $kgUnit->id);

        // ========== 10. FARINES & SEMOULES (Marché Agro-Alimentaire) ==========
        $this->seedFarines($categories, $kgUnit->id);

        // ========== 11. HUILES & CORPS GRAS (Marché Agro-Alimentaire) ==========
        $this->seedHuiles($categories, $kgUnit->id);

        // ========== 12. PRODUITS FERMENTÉS (Marché Agro-Alimentaire) ==========
        $this->seedProduitsFermentes($categories, $kgUnit->id);

        // ========== 13. FRUITS & LÉGUMES TRANSFORMÉS (Marché Agro-Alimentaire) ==========
        $this->seedFruitsLegumesTransformes($categories, $kgUnit->id);

        // ========== 14. BOISSONS & JUS (Marché Agro-Alimentaire) ==========
        $this->seedBoissons($categories, $kgUnit->id);

        // ========== 15. PRODUITS BIO & SPÉCIALITÉS (Marché Agro-Alimentaire) ==========
        $this->seedProduitsBio($categories, $kgUnit->id);

        $this->command->info("\n✅ " . $this->totalCrops . ' cultures créées avec succès !');
    }

    private function seedCereales($categories, $unitId)
    {
        $cat = $categories['Céréales'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Maïs',
                'varieties' => ['blanc', 'jaune', 'rouge', 'grain', 'épis', 'pop-corn'],
                'scientific' => 'Zea mays',
                'days' => 90,
                'seasons' => ['Mai-Juillet', 'Septembre-Novembre']
            ],
            [
                'name' => 'Riz',
                'varieties' => ['paddy', 'étuvé', 'blanchi', 'brisures', 'parfumé', 'basmati'],
                'scientific' => 'Oryza sativa',
                'days' => 120,
                'seasons' => ['Juin-Octobre', 'Toute année']
            ],
            [
                'name' => 'Mil',
                'varieties' => ['perlé', 'rouge', 'blanc', 'glume'],
                'scientific' => 'Pennisetum glaucum',
                'days' => 75,
                'seasons' => ['Mai-Septembre']
            ],
            [
                'name' => 'Sorgho',
                'varieties' => ['blanc', 'rouge', 'brun', 'doux', 'amer'],
                'scientific' => 'Sorghum bicolor',
                'days' => 85,
                'seasons' => ['Juin-Octobre']
            ],
            [
                'name' => 'Fonio',
                'varieties' => ['décortiqué', 'précuit', 'blanc', 'noir'],
                'scientific' => 'Digitaria exilis',
                'days' => 60,
                'seasons' => ['Juillet-Septembre']
            ],
            [
                'name' => 'Blé',
                'varieties' => ['tendre', 'dur', 'poulard'],
                'scientific' => 'Triticum aestivum',
                'days' => 100,
                'seasons' => ['Novembre-Mai']
            ],
            [
                'name' => 'Orge',
                'varieties' => ['de brasserie', 'fourragère', 'mondée'],
                'scientific' => 'Hordeum vulgare',
                'days' => 90,
                'seasons' => ['Octobre-Juin']
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedLegumineuses($categories, $unitId)
    {
        $cat = $categories['Légumineuses'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Haricot',
                'varieties' => ['rouge', 'blanc', 'noir', 'vert', 'beurre', 'mungo'],
                'scientific' => 'Phaseolus vulgaris',
                'days' => 70,
                'seasons' => ['Mars-Juin', 'Août-Novembre']
            ],
            [
                'name' => 'Niébé',
                'varieties' => ['blanc', 'noir', 'rouge', 'à œil noir'],
                'scientific' => 'Vigna unguiculata',
                'days' => 65,
                'seasons' => ['Juin-Octobre']
            ],
            [
                'name' => 'Arachide',
                'varieties' => ['coque', 'décortiquée', 'grillée', 'rouge'],
                'scientific' => 'Arachis hypogaea',
                'days' => 120,
                'seasons' => ['Mai-Octobre']
            ],
            [
                'name' => 'Soja',
                'varieties' => ['jaune', 'noir', 'vert'],
                'scientific' => 'Glycine max',
                'days' => 100,
                'seasons' => ['Juin-Novembre']
            ],
            [
                'name' => 'Pois',
                'varieties' => ['chiche', 'cassé', 'd\'Angole', 'pigeon'],
                'scientific' => 'Cicer arietinum',
                'days' => 90,
                'seasons' => ['Octobre-Mars']
            ],
            [
                'name' => 'Lentille',
                'varieties' => ['verte', 'corail', 'brune', 'noire'],
                'scientific' => 'Lens culinaris',
                'days' => 80,
                'seasons' => ['Novembre-Avril']
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedFruits($categories, $unitId)
    {
        $cat = $categories['Fruits'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Mangue',
                'varieties' => ['Kent', 'Keitt', 'Amélie', 'Brooks', 'Palmer', 'Julie'],
                'scientific' => 'Mangifera indica',
                'seasons' => ['Mars-Juillet', 'Octobre-Décembre']
            ],
            [
                'name' => 'Banane',
                'varieties' => ['dessert', 'plantain', 'mini', 'figue'],
                'scientific' => 'Musa acuminata',
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Ananas',
                'varieties' => ['Cayenne', 'Queen', 'Victoria', 'Smooth', 'MD2'],
                'scientific' => 'Ananas comosus',
                'seasons' => ['Décembre-Juin']
            ],
            [
                'name' => 'Orange',
                'varieties' => ['doute de Malaisie', 'Valencia', 'Navel', 'Hamlin'],
                'scientific' => 'Citrus sinensis',
                'seasons' => ['Novembre-Mai']
            ],
            [
                'name' => 'Citron',
                'varieties' => ['vert', 'jaune', 'Meyer', 'Eureka'],
                'scientific' => 'Citrus limon',
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Papaye',
                'varieties' => ['rouge', 'jaune', 'solo', 'formosa'],
                'scientific' => 'Carica papaya',
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Avocat',
                'varieties' => ['Hass', 'Fuerte', 'Ettinger', 'Bacon', 'Zutano'],
                'scientific' => 'Persea americana',
                'seasons' => ['Octobre-Mars']
            ],
            [
                'name' => 'Pastèque',
                'varieties' => ['rouge', 'jaune', 'sans pépins'],
                'scientific' => 'Citrullus lanatus',
                'days' => 85,
                'seasons' => ['Février-Mai', 'Août-Novembre']
            ],
            [
                'name' => 'Goyave',
                'varieties' => ['blanche', 'rose', 'fraise'],
                'scientific' => 'Psidium guajava',
                'seasons' => ['Août-Décembre']
            ],
            [
                'name' => 'Fruit de la passion',
                'varieties' => ['pourpre', 'jaune', 'gigantesque'],
                'scientific' => 'Passiflora edulis',
                'seasons' => ['Mai-Octobre']
            ],
            [
                'name' => 'Mandarine',
                'varieties' => ['clémentine', 'tangerine', 'satsuma'],
                'scientific' => 'Citrus reticulata',
                'seasons' => ['Novembre-Février']
            ],
            [
                'name' => 'Pamplemousse',
                'varieties' => ['blanc', 'rose', 'rouge'],
                'scientific' => 'Citrus paradisi',
                'seasons' => ['Décembre-Mai']
            ],
            [
                'name' => 'Noix de coco',
                'varieties' => ['verte', 'sèche', 'naine'],
                'scientific' => 'Cocos nucifera',
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Pomme',
                'varieties' => ['golden', 'gala', 'granny', 'fuji'],
                'scientific' => 'Malus domestica',
                'seasons' => ['Septembre-Novembre']
            ],
            [
                'name' => 'Corossol',
                'varieties' => ['vert', 'épineux'],
                'scientific' => 'Annona muricata',
                'seasons' => ['Juin-Décembre']
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedLegumes($categories, $unitId)
    {
        $cat = $categories['Légumes'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Tomate',
                'varieties' => ['cœur de bœuf', 'cerise', 'grappe', 'ronde', 'olivette'],
                'scientific' => 'Solanum lycopersicum',
                'days' => 75,
                'seasons' => ['Octobre-Mars', 'Avril-Juillet']
            ],
            [
                'name' => 'Oignon',
                'varieties' => ['blanc', 'jaune', 'rouge', 'échalote', 'cébette'],
                'scientific' => 'Allium cepa',
                'days' => 150,
                'seasons' => ['Novembre-Avril']
            ],
            [
                'name' => 'Aubergine',
                'varieties' => ['violette', 'blanche', 'africaine', 'japonaise'],
                'scientific' => 'Solanum melongena',
                'days' => 70,
                'seasons' => ['Mars-Octobre']
            ],
            [
                'name' => 'Gombo',
                'varieties' => ['vert', 'rouge', 'nain'],
                'scientific' => 'Abelmoschus esculentus',
                'days' => 60,
                'seasons' => ['Mai-Octobre']
            ],
            [
                'name' => 'Piment',
                'varieties' => ['frais', 'séché', 'en poudre', 'bird eye', 'habanero'],
                'scientific' => 'Capsicum frutescens',
                'days' => 80,
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Poivron',
                'varieties' => ['vert', 'rouge', 'jaune', 'orange'],
                'scientific' => 'Capsicum annuum',
                'days' => 75,
                'seasons' => ['Mars-Octobre']
            ],
            [
                'name' => 'Chou',
                'varieties' => ['pommé', 'frisé', 'rouge', 'de Chine'],
                'scientific' => 'Brassica oleracea',
                'days' => 90,
                'seasons' => ['Septembre-Mars']
            ],
            [
                'name' => 'Concombre',
                'varieties' => ['vert', 'cornichon'],
                'scientific' => 'Cucumis sativus',
                'days' => 55,
                'seasons' => ['Mars-Octobre']
            ],
            [
                'name' => 'Courgette',
                'varieties' => ['verte', 'jaune', 'ronde'],
                'scientific' => 'Cucurbita pepo',
                'days' => 50,
                'seasons' => ['Mai-Octobre']
            ],
            [
                'name' => 'Haricot vert',
                'varieties' => ['filet', 'mangetout'],
                'scientific' => 'Phaseolus vulgaris',
                'days' => 55,
                'seasons' => ['Avril-Octobre']
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedLegumesRacines($categories, $unitId)
    {
        $cat = $categories['Légumes racines'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Manioc',
                'varieties' => ['doux', 'amer', 'bonoua', 'improved'],
                'scientific' => 'Manihot esculenta',
                'days' => 270,
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Igname',
                'varieties' => ['blanche', 'jaune', 'eau', 'kponan', 'florido'],
                'scientific' => 'Dioscorea alata',
                'days' => 240,
                'seasons' => ['Août-Décembre']
            ],
            [
                'name' => 'Patate douce',
                'varieties' => ['orange', 'blanche', 'violette', 'japonaise'],
                'scientific' => 'Ipomoea batatas',
                'days' => 120,
                'seasons' => ['Permanente']
            ],
            [
                'name' => 'Pomme de terre',
                'varieties' => ['consommation', 'frite', 'chair ferme', 'primeur'],
                'scientific' => 'Solanum tuberosum',
                'days' => 90,
                'seasons' => ['Novembre-Mars', 'Avril-Octobre']
            ],
            [
                'name' => 'Taro',
                'varieties' => ['macabo', 'malanga', 'chou caraïbe'],
                'scientific' => 'Colocasia esculenta',
                'days' => 210,
                'seasons' => ['Mai-Novembre']
            ],
            [
                'name' => 'Carotte',
                'varieties' => ['orange', 'jaune', 'violette', 'blanche'],
                'scientific' => 'Daucus carota',
                'days' => 70,
                'seasons' => ['Septembre-Mai']
            ],
            [
                'name' => 'Betterave',
                'varieties' => ['rouge', 'jaune', 'chioggia'],
                'scientific' => 'Beta vulgaris',
                'days' => 80,
                'seasons' => ['Août-Mars']
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedProduitsElevage($categories, $unitId)
    {
        $cat = $categories['Produits d\'élevage'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Bovin',
                'varieties' => ['zébu', 'taurillon', 'vache', 'veau', 'génisse'],
                'scientific' => 'Bos taurus',
            ],
            [
                'name' => 'Ovin',
                'varieties' => ['mouton', 'agneau', 'brebis'],
                'scientific' => 'Ovis aries',
            ],
            [
                'name' => 'Caprin',
                'varieties' => ['chèvre', 'bouc', 'chevreau'],
                'scientific' => 'Capra aegagrus',
            ],
            [
                'name' => 'Poulet',
                'varieties' => ['chair', 'ponte', 'traditionnel', 'label rouge'],
                'scientific' => 'Gallus gallus',
                'days' => 45,
            ],
            [
                'name' => 'Pintade',
                'varieties' => ['commune', 'vulturine'],
                'scientific' => 'Numida meleagris',
                'days' => 70,
            ],
            [
                'name' => 'Canard',
                'varieties' => ['colvert', 'barbarie', 'mulard'],
                'scientific' => 'Anas platyrhynchos',
                'days' => 60,
            ],
            [
                'name' => 'Œuf',
                'varieties' => ['poule', 'caille', 'canard'],
            ],
            [
                'name' => 'Lait',
                'varieties' => ['vache', 'chèvre', 'brebis'],
            ],
            [
                'name' => 'Poisson frais',
                'varieties' => ['tilapia', 'carpe', 'capitaine', 'mâchoiron', 'sardinelle'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedProduitsExport($categories, $unitId)
    {
        $cat = $categories['Produits d\'exportation'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Cacao',
                'varieties' => ['Forastero', 'Criollo', 'Trinitario', 'National'],
                'scientific' => 'Theobroma cacao',
                'seasons' => ['Octobre-Mars', 'Avril-Septembre']
            ],
            [
                'name' => 'Café',
                'varieties' => ['Arabica', 'Robusta', 'Liberica'],
                'scientific' => 'Coffea canephora',
                'seasons' => ['Octobre-Mars']
            ],
            [
                'name' => 'Noix de cajou',
                'varieties' => ['brute', 'blanchie', 'grillée'],
                'scientific' => 'Anacardium occidentale',
                'seasons' => ['Février-Mai']
            ],
            [
                'name' => 'Sésame',
                'varieties' => ['blanc', 'noir', 'doré'],
                'scientific' => 'Sesamum indicum',
                'days' => 90,
                'seasons' => ['Juillet-Octobre']
            ],
            [
                'name' => 'Karité',
                'varieties' => ['amandes'],
                'scientific' => 'Vitellaria paradoxa',
                'seasons' => ['Mai-Septembre']
            ],
            [
                'name' => 'Poivre',
                'varieties' => ['noir', 'blanc', 'vert', 'rouge'],
                'scientific' => 'Piper nigrum',
                'days' => 270,
                'seasons' => ['Décembre-Mars']
            ],
            [
                'name' => 'Gingembre',
                'varieties' => ['frais', 'sec', 'en poudre'],
                'scientific' => 'Zingiber officinale',
                'days' => 210,
                'seasons' => ['Décembre-Mars']
            ],
            [
                'name' => 'Coton',
                'varieties' => ['graine', 'fibre'],
                'scientific' => 'Gossypium hirsutum',
                'days' => 180,
                'seasons' => ['Mai-Novembre']
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedSemences($categories, $unitId)
    {
        $cat = $categories['Semences de céréales'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Semences de maïs',
                'varieties' => ['hybride', 'variété locale', 'bio'],
                'days' => 90,
            ],
            [
                'name' => 'Semences de riz',
                'varieties' => ['NERICA', 'irrigué', 'pluvial'],
                'days' => 120,
            ],
            [
                'name' => 'Semences de mil',
                'varieties' => ['hâtif', 'tardif', 'résistant sécheresse'],
                'days' => 75,
            ],
            [
                'name' => 'Semences de sorgho',
                'varieties' => ['grain', 'fourrager'],
                'days' => 85,
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedIntrantsElevage($categories, $unitId)
    {
        $cat = $categories['Aliments bétail'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Provende volaille',
                'varieties' => ['démarrage', 'croissance', 'ponte'],
            ],
            [
                'name' => 'Provende bovin',
                'varieties' => ['engraissement', 'laitier'],
            ],
            [
                'name' => 'Tourteau de coton',
                'varieties' => ['bovins', 'ovins'],
            ],
            [
                'name' => 'Tourteau d\'arachide',
                'varieties' => ['bovins', 'porcins'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedFarines($categories, $unitId)
    {
        $cat = $categories['Farines de céréales'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Farine de maïs',
                'varieties' => ['fine', 'grossière', 'torréfiée'],
            ],
            [
                'name' => 'Farine de mil',
                'varieties' => ['fine', 'grossière'],
            ],
            [
                'name' => 'Farine de sorgho',
                'varieties' => ['blanche', 'rouge'],
            ],
            [
                'name' => 'Farine de blé',
                'varieties' => ['T55', 'T65', 'complète'],
            ],
            [
                'name' => 'Farine de riz',
                'varieties' => ['blanche', 'complète'],
            ],
            [
                'name' => 'Farine de manioc',
                'varieties' => ['blanche', 'torréfiée'],
            ],
            [
                'name' => 'Semoule de maïs',
                'varieties' => ['fine', 'moyenne', 'grosse'],
            ],
            [
                'name' => 'Brisures de riz',
                'varieties' => ['fines', 'moyennes'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedHuiles($categories, $unitId)
    {
        $cat = $categories['Huiles végétales'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Huile de palme',
                'varieties' => ['rouge', 'raffinée'],
            ],
            [
                'name' => 'Huile d\'arachide',
                'varieties' => ['vierge', 'raffinée'],
            ],
            [
                'name' => 'Huile de coco',
                'varieties' => ['vierge', 'raffinée'],
            ],
            [
                'name' => 'Beurre de karité',
                'varieties' => ['brut', 'raffiné', 'cosmétique'],
            ],
            [
                'name' => 'Beurre d\'arachide',
                'varieties' => ['lisse', 'croustillant', 'nature', 'sucré'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedProduitsFermentes($categories, $unitId)
    {
        $cat = $categories['Produits fermentés'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Attiéké',
                'varieties' => ['fin', 'gros', 'rouge'],
            ],
            [
                'name' => 'Gari',
                'varieties' => ['fin', 'gros'],
            ],
            [
                'name' => 'Cossettes d\'igname',
                'varieties' => ['blanches', 'jaunes'],
            ],
            [
                'name' => 'Cossettes de manioc',
                'varieties' => ['sèches'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedFruitsLegumesTransformes($categories, $unitId)
    {
        $cat = $categories['Fruits séchés'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Mangues séchées',
                'varieties' => ['bandes', 'morceaux'],
            ],
            [
                'name' => 'Bananes séchées',
                'varieties' => ['entières', 'chips'],
            ],
            [
                'name' => 'Gombos séchés',
                'varieties' => ['entiers', 'en poudre'],
            ],
            [
                'name' => 'Oignons séchés',
                'varieties' => ['paillettes', 'en poudre'],
            ],
            [
                'name' => 'Purée de tomate',
                'varieties' => ['nature', 'concentrée'],
            ],
            [
                'name' => 'Confitures',
                'varieties' => ['mangue', 'goyave', 'ananas'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedBoissons($categories, $unitId)
    {
        $cat = $categories['Boissons & Jus'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Jus de bissap',
                'varieties' => ['pur', 'concentré', 'gazeux'],
            ],
            [
                'name' => 'Jus de gingembre',
                'varieties' => ['pur', 'pétillant'],
            ],
            [
                'name' => 'Jus de tamarin',
                'varieties' => ['pur', 'sucré'],
            ],
            [
                'name' => 'Jus de mangue',
                'varieties' => ['pur', 'nectar'],
            ],
            [
                'name' => 'Jus d\'ananas',
                'varieties' => ['pur', 'concentré'],
            ],
            [
                'name' => 'Sirops de fruits',
                'varieties' => ['mangue', 'goyave', 'gingembre'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function seedProduitsBio($categories, $unitId)
    {
        $cat = $categories['Produits bio & spécialités'] ?? null;
        if (!$cat) return;

        $crops = [
            [
                'name' => 'Miel bio',
                'varieties' => ['toutes fleurs', 'acacia', 'forêt', 'mangue'],
            ],
            [
                'name' => 'Plantes médicinales',
                'varieties' => ['moringa', 'citronnelle', 'verveine', 'menthe'],
            ],
            [
                'name' => 'Épices bio',
                'varieties' => ['poivre', 'piment', 'curcuma'],
            ],
        ];

        $this->createCrops($crops, $cat->id, $unitId);
    }

    private function createCrops($crops, $categoryId, $defaultUnitId)
    {
        foreach ($crops as $cropData) {
            $description = $cropData['description'] ?? $cropData['name'] . ' - ' . implode(', ', $cropData['varieties'] ?? []);

            Crop::updateOrCreate(
                [
                    'name' => $cropData['name'],
                    'category_id' => $categoryId,
                ],
                [
                    'variety' => $cropData['varieties'] ?? [],
                    'scientific_name' => $cropData['scientific'] ?? null,
                    'description' => $description,
                    'category_id' => $categoryId,
                    'default_unit_id' => $defaultUnitId,
                    'growing_seasons' => $cropData['seasons'] ?? [],
                    'growing_days' => $cropData['days'] ?? null,
                    'is_active' => true,
                ]
            );

            $this->totalCrops++;
            $this->command->getOutput()->write('.');
        }
    }
}
