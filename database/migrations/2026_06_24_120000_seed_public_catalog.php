<?php

use App\Models\SiteSetting;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $settings = [
            'hero_title' => "Location d'autocars de luxe pour vos voyages, evenements et missions.",
            'hero_text' => 'Une experience de reservation claire, un parc valorise, des demandes suivies et un retour qualite apres chaque prestation.',
            'catalog_title' => 'Choisir le bon car selon la capacite et le standing.',
            'phone' => '+225 07 00 00 00 00',
            'email' => 'utb.location-af@utb-ci.net',
            'whatsapp' => '2250707055229',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $vehicles = [
            ['name' => 'Autocar VIP 45 places', 'slug' => 'autocar-vip-45', 'category' => 'vip', 'capacity' => 45, 'status' => 'Disponible', 'description' => 'Ideal pour les delegations, directions, evenements officiels et missions sensibles.', 'equipment' => ['Climatisation', 'Sieges inclinables', 'Ecran TV', 'Micro', 'USB', 'Grand bagage'], 'position' => 1],
            ['name' => 'Autocar Premium 50 places', 'slug' => 'autocar-premium-50', 'category' => 'premium', 'capacity' => 50, 'status' => 'Disponible', 'description' => 'Le format polyvalent pour seminaires, sorties scolaires, tourisme et ceremonies.', 'equipment' => ['Climatisation', 'TV', 'Sonorisation', 'Sieges confort', 'Bagagerie'], 'position' => 2],
            ['name' => 'Grand Tourisme 60 places', 'slug' => 'grand-tourisme-60', 'category' => 'grand-tourisme', 'capacity' => 60, 'status' => 'Sur demande', 'description' => 'Grande capacite pour voyages de groupe, associations, clubs sportifs et longues distances.', 'equipment' => ['Climatisation', 'Toilettes selon parc', 'TV', 'Micro', 'Bagagerie XL'], 'position' => 3],
            ['name' => 'Car Confort 30 places', 'slug' => 'car-confort-30', 'category' => 'premium', 'capacity' => 30, 'status' => 'Disponible', 'description' => "Une solution agile pour groupes reduits, formations, transferts et sorties d'une journee.", 'equipment' => ['Climatisation', 'Sieges confort', 'Bagages', 'Excursions'], 'position' => 4],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(['slug' => $vehicle['slug']], $vehicle);
        }
    }

    public function down(): void
    {
        //
    }
};
