<?php
// app/Console/Commands/FixMerchantRoles.php

// namespace App\Console\Commands;

// use App\Models\MerchantProfile;
// use Illuminate\Console\Command;

// class FixMerchantRoles extends Command
// {
//     protected $signature = 'fix:merchant-roles';
//     protected $description = 'Ajoute le role merchant aux utilisateurs qui ont un merchant profile';

//     public function handle()
//     {
//         $this->info('Mise à jour des rôles merchant...');

//         $count = 0;
//         $profiles = MerchantProfile::all();

//         foreach ($profiles as $profile) {
//             $user = $profile->user;

//             // IGNORER super_admin ET admin
//             if ($user &&
//                 !$user->hasRole('super_admin') &&
//                 !$user->hasRole('admin') &&
//                 !$user->hasRole('merchant')) {

//                 $user->assignRole('merchant');
//                 $count++;
//                 $this->line("✓ Role merchant ajouté à: {$user->email}");
//             }
//         }

//         $this->info("Terminé! {$count} utilisateur(s) mis à jour.");
//     }
// }
