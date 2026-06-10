<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ReferenceGenerator
{
    /**
     * Génère une référence unique pour n'importe quel module.
     * * @param string $prefix Le préfixe (ex: CMD, TX, DEP)
     * @param int $randomLength Longueur de la chaîne aléatoire
     * @return string format: PREFIX-YYYYMMDD-RANDOM
     */
    public static function generate(string $prefix = 'TX', int $randomLength = 6): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random($randomLength));

        return sprintf('%s-%s-%s', strtoupper($prefix), $date, $random);
    }
}
