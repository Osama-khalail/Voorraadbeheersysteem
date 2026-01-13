<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Category;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class MaterialenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Maak meerdere categorieën aan (of haal ze op als ze al bestaan)
        $categoryDefinitions = [
            ['naam' => 'Regel apparatuur', 'omschrijving' => 'Apparatuur voor regeling en automatisering'],
            ['naam' => 'Sensoren', 'omschrijving' => 'Diverse sensoren voor metingen en monitoring'],
            ['naam' => 'Actuatoren', 'omschrijving' => 'Actuatoren voor aansturing en beweging'],
            ['naam' => 'Kabels', 'omschrijving' => 'Bekabeling en aansluitmaterialen'],
            ['naam' => 'Gereedschap', 'omschrijving' => 'Hand- en meetgereedschap'],
            ['naam' => 'Behuizingen', 'omschrijving' => 'Kasten en behuizingen voor apparatuur'],
            ['naam' => 'Accessoires', 'omschrijving' => 'Aanvullende onderdelen en accessoires'],
        ];

        $categories = [];
        foreach ($categoryDefinitions as $def) {
            $categories[] = Category::firstOrCreate(
                ['naam' => $def['naam']],
                [
                    'slug' => Str::slug($def['naam']),
                    'omschrijving' => $def['omschrijving']
                ]
            );
        }

        // Maak 100 materialen aan en verdeel ze over de categorieën
        for ($i = 1; $i <= 100; $i++) {
            $index = str_pad($i, 3, '0', STR_PAD_LEFT);
            $type = 'EY6IO30F00' . $index;
            $leveranciers = ['Sauter','Remedial','Honeywell'];
            $leverancier = $leveranciers[array_rand($leveranciers)];
            $omschrijving = 'Modbus 360-IO ' . $index;
            $naam = $type . ' - ' . $omschrijving;
            // Kies een willekeurige categorie voor dit product
            $assignedCategory = $categories[array_rand($categories)];

            $product = Product::create([
                'naam' => $naam,
                'type' => $type,
                'leverancier' => $leverancier,
                'omschrijving' => $omschrijving,
                'minimale_voorraad' => 5,
                'foto_url' => null,
                'categorie_id' => $assignedCategory->id,
            ]);

            // initial stock (random 0-20)
            $initial = rand(0, 20);
            Stock::create([
                'product_id' => $product->id,
                'aantal' => $initial,
                'laatst_aangepast_op' => now(),
                'laatst_aangepast_door' => 1,
            ]);
        }
    }
}
