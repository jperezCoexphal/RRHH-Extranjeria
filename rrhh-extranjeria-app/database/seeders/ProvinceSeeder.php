<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Seed the provinces table with Spanish provinces (INE codes).
     */
    public function run(): void
    {
        $spain = Country::where('iso_code_2', 'ES')->first();

        if (!$spain) {
            $this->command->error('Spain not found in countries table. Run CountrySeeder first.');
            return;
        }

        // Provincias de España con código INE de 2 dígitos
        $provinces = [
            ['province_code' => '01', 'province_name' => 'Álava'],
            ['province_code' => '02', 'province_name' => 'Albacete'],
            ['province_code' => '03', 'province_name' => 'Alicante'],
            ['province_code' => '04', 'province_name' => 'Almería'],
            ['province_code' => '05', 'province_name' => 'Ávila'],
            ['province_code' => '06', 'province_name' => 'Badajoz'],
            ['province_code' => '07', 'province_name' => 'Illes Balears'],
            ['province_code' => '08', 'province_name' => 'Barcelona'],
            ['province_code' => '09', 'province_name' => 'Burgos'],
            ['province_code' => '10', 'province_name' => 'Cáceres'],
            ['province_code' => '11', 'province_name' => 'Cádiz'],
            ['province_code' => '12', 'province_name' => 'Castellón'],
            ['province_code' => '13', 'province_name' => 'Ciudad Real'],
            ['province_code' => '14', 'province_name' => 'Córdoba'],
            ['province_code' => '15', 'province_name' => 'A Coruña'],
            ['province_code' => '16', 'province_name' => 'Cuenca'],
            ['province_code' => '17', 'province_name' => 'Girona'],
            ['province_code' => '18', 'province_name' => 'Granada'],
            ['province_code' => '19', 'province_name' => 'Guadalajara'],
            ['province_code' => '20', 'province_name' => 'Gipuzkoa'],
            ['province_code' => '21', 'province_name' => 'Huelva'],
            ['province_code' => '22', 'province_name' => 'Huesca'],
            ['province_code' => '23', 'province_name' => 'Jaén'],
            ['province_code' => '24', 'province_name' => 'León'],
            ['province_code' => '25', 'province_name' => 'Lleida'],
            ['province_code' => '26', 'province_name' => 'La Rioja'],
            ['province_code' => '27', 'province_name' => 'Lugo'],
            ['province_code' => '28', 'province_name' => 'Madrid'],
            ['province_code' => '29', 'province_name' => 'Málaga'],
            ['province_code' => '30', 'province_name' => 'Murcia'],
            ['province_code' => '31', 'province_name' => 'Navarra'],
            ['province_code' => '32', 'province_name' => 'Ourense'],
            ['province_code' => '33', 'province_name' => 'Asturias'],
            ['province_code' => '34', 'province_name' => 'Palencia'],
            ['province_code' => '35', 'province_name' => 'Las Palmas'],
            ['province_code' => '36', 'province_name' => 'Pontevedra'],
            ['province_code' => '37', 'province_name' => 'Salamanca'],
            ['province_code' => '38', 'province_name' => 'Santa Cruz de Tenerife'],
            ['province_code' => '39', 'province_name' => 'Cantabria'],
            ['province_code' => '40', 'province_name' => 'Segovia'],
            ['province_code' => '41', 'province_name' => 'Sevilla'],
            ['province_code' => '42', 'province_name' => 'Soria'],
            ['province_code' => '43', 'province_name' => 'Tarragona'],
            ['province_code' => '44', 'province_name' => 'Teruel'],
            ['province_code' => '45', 'province_name' => 'Toledo'],
            ['province_code' => '46', 'province_name' => 'Valencia'],
            ['province_code' => '47', 'province_name' => 'Valladolid'],
            ['province_code' => '48', 'province_name' => 'Bizkaia'],
            ['province_code' => '49', 'province_name' => 'Zamora'],
            ['province_code' => '50', 'province_name' => 'Zaragoza'],
            ['province_code' => '51', 'province_name' => 'Ceuta'],
            ['province_code' => '52', 'province_name' => 'Melilla'],
        ];

        foreach ($provinces as $province) {
            Province::firstOrCreate(
                ['province_code' => $province['province_code']],
                [
                    'province_name' => $province['province_name'],
                    'province_code' => $province['province_code'],
                    'country_id' => $spain->id,
                ]
            );
        }

        $this->command->info('Seeded ' . count($provinces) . ' Spanish provinces.');
    }
}
