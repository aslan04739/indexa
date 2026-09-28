<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Wallet;
use Illuminate\Database\Seeder;

/** Demo data for local development. All accounts use the password "password". */
class DatabaseSeeder extends Seeder
{
    public function run(Wallet $wallet): void
    {
        User::factory()->create(['name' => 'Admin Indexa', 'email' => 'admin@indexa.test', 'role' => User::ADMIN]);
        $publisher = User::factory()->create(['name' => 'Éditeur Démo', 'email' => 'publisher@indexa.test', 'role' => User::PUBLISHER]);
        $buyer = User::factory()->create(['name' => 'Annonceur Démo', 'email' => 'buyer@indexa.test', 'role' => User::BUYER, 'company' => 'Démo SARL']);

        $demoSites = [
            ['Actu Tech DZ', 'actutech-demo.dz', 'fr', 'tech', 18000, 120000, 32],
            ['Iqtisad Online', 'iqtisad-demo.dz', 'ar', 'finance', 25000, 260000, 41],
            ['Voyage Algérie', 'voyage-demo.dz', 'fr', 'travel', 9000, 45000, 18],
            ['Sahha News', 'sahha-demo.dz', 'ar', 'health', 12000, 90000, 24],
            ['Auto DZ Mag', 'autodz-demo.dz', 'fr', 'auto', 15000, 70000, 27],
            ['Algeria Business Review', 'abr-demo.dz', 'en', 'business', 30000, 35000, 36],
        ];

        foreach ($demoSites as [$name, $domain, $language, $category, $price, $traffic, $dr]) {
            Site::factory()->approved()->for($publisher)->create([
                'name' => $name, 'domain' => $domain, 'url' => 'https://'.$domain,
                'language' => $language, 'category' => $category,
                'price_dzd' => $price, 'monthly_traffic' => $traffic, 'domain_rating' => $dr,
            ]);
        }

        Site::factory()->for($publisher)->create(['name' => 'Site en attente', 'domain' => 'pending-demo.dz', 'url' => 'https://pending-demo.dz']);

        $wallet->credit($buyer, 100000, WalletTransaction::TOPUP, null, 'Demo top-up');
    }
}
