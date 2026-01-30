<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Models\Address;
use App\Models\Country;
use App\Models\Foreigner;
use App\Models\ForeignerExtraData;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Database\Seeder;

class ForeignerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando trabajadores extranjeros...');

        // Países africanos
        $countries = Country::whereIn('iso_code_2', [
            'MA', 'SN', 'NG', 'ML', 'GH', 'CM', 'CI', 'GN', 'MR', 'GM',
        ])->get()->keyBy('iso_code_2');

        $required = ['MA', 'SN', 'NG', 'ML', 'GH', 'CM', 'CI', 'GN', 'MR', 'GM'];
        $missing = collect($required)->diff($countries->keys());

        if ($missing->isNotEmpty()) {
            $this->command->warn("Países no encontrados: {$missing->implode(', ')}. Se omitirán esos registros.");
        }

        // España y provincias para direcciones de residencia
        $spain = Country::where('iso_code_2', 'ES')->first();

        $provinces = collect(['Huelva', 'Almería', 'Jaén', 'Murcia', 'Lleida'])
            ->mapWithKeys(function ($name) {
                $province = Province::where('province_name', $name)->first();
                $municipality = $province ? Municipality::where('province_id', $province->id)->first() : null;
                return [$name => compact('province', 'municipality')];
            });

        $foreigners = [
            // 1. Marruecos - Hombre casado
            [
                'foreigner' => [
                    'first_name' => 'Youssef',
                    'last_name' => 'Belhaj',
                    'passport' => 'MA23456001',
                    'nie' => 'X1234501A',
                    'niss' => '281200000101',
                    'gender' => Gender::H->value,
                    'birthdate' => '1987-04-12',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'MA',
                    'birthplace_name' => 'Beni Mellal',
                ],
                'extra' => [
                    'father_name' => 'Hassan Belhaj',
                    'mother_name' => 'Khadija Amrani',
                    'phone' => '612001001',
                    'email' => 'y.belhaj@email.com',
                ],
                'address' => ['street_name' => 'Calle Rábida', 'number' => '14', 'floor_door' => '2ºA', 'postal_code' => '21001', 'provincia' => 'Huelva'],
            ],
            // 2. Marruecos - Mujer soltera
            [
                'foreigner' => [
                    'first_name' => 'Naima',
                    'last_name' => 'Ouazzani',
                    'passport' => 'MA23456002',
                    'nie' => 'X1234502B',
                    'niss' => '281200000102',
                    'gender' => Gender::M->value,
                    'birthdate' => '1993-08-25',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'MA',
                    'birthplace_name' => 'Kenitra',
                ],
                'extra' => [
                    'father_name' => 'Abdelkrim Ouazzani',
                    'mother_name' => 'Latifa Chraibi',
                    'phone' => '612001002',
                    'email' => 'naima.ouazzani@email.com',
                ],
                'address' => ['street_name' => 'Calle Concepción', 'number' => '8', 'floor_door' => '1ºB', 'postal_code' => '21002', 'provincia' => 'Huelva'],
            ],
            // 3. Senegal - Hombre soltero
            [
                'foreigner' => [
                    'first_name' => 'Ibrahima',
                    'last_name' => 'Ndiaye',
                    'passport' => 'SN34567001',
                    'nie' => 'X2345601C',
                    'niss' => '281200000103',
                    'gender' => Gender::H->value,
                    'birthdate' => '1991-01-10',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'SN',
                    'birthplace_name' => 'Thiès',
                ],
                'extra' => [
                    'father_name' => 'Ousmane Ndiaye',
                    'mother_name' => 'Fatou Diop',
                    'phone' => '623001001',
                    'email' => 'ibrahima.ndiaye@email.com',
                ],
                'address' => ['street_name' => 'Calle Perú', 'number' => '22', 'floor_door' => '3ºC', 'postal_code' => '04004', 'provincia' => 'Almería'],
            ],
            // 4. Senegal - Hombre casado
            [
                'foreigner' => [
                    'first_name' => 'Mamadou',
                    'last_name' => 'Ba',
                    'passport' => 'SN34567002',
                    'nie' => 'X2345602D',
                    'niss' => '281200000104',
                    'gender' => Gender::H->value,
                    'birthdate' => '1985-06-18',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'SN',
                    'birthplace_name' => 'Saint-Louis',
                ],
                'extra' => [
                    'father_name' => 'Abdoulaye Ba',
                    'mother_name' => 'Mariama Sow',
                    'phone' => '623001002',
                    'email' => 'mamadou.ba@email.com',
                ],
                'address' => ['street_name' => 'Avda. Federico García Lorca', 'number' => '45', 'floor_door' => '4ºD', 'postal_code' => '04006', 'provincia' => 'Almería'],
            ],
            // 5. Nigeria - Mujer casada
            [
                'foreigner' => [
                    'first_name' => 'Blessing',
                    'last_name' => 'Adeyemi',
                    'passport' => 'NG45678001',
                    'nie' => 'X3456701E',
                    'niss' => '281200000105',
                    'gender' => Gender::M->value,
                    'birthdate' => '1994-12-03',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'NG',
                    'birthplace_name' => 'Benin City',
                ],
                'extra' => [
                    'father_name' => 'Samuel Adeyemi',
                    'mother_name' => 'Grace Adeyemi',
                    'phone' => '634001001',
                    'email' => 'b.adeyemi@email.com',
                ],
                'address' => ['street_name' => 'Calle Lineros', 'number' => '5', 'floor_door' => null, 'postal_code' => '23003', 'provincia' => 'Jaén'],
            ],
            // 6. Mali - Hombre soltero
            [
                'foreigner' => [
                    'first_name' => 'Seydou',
                    'last_name' => 'Traoré',
                    'passport' => 'ML56789001',
                    'nie' => 'X4567801F',
                    'niss' => '281200000106',
                    'gender' => Gender::H->value,
                    'birthdate' => '1989-03-27',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'ML',
                    'birthplace_name' => 'Sikasso',
                ],
                'extra' => [
                    'father_name' => 'Amadou Traoré',
                    'mother_name' => 'Fatoumata Coulibaly',
                    'phone' => '645001001',
                    'email' => 'seydou.traore@email.com',
                ],
                'address' => ['street_name' => 'Calle Bernabé Soriano', 'number' => '31', 'floor_door' => '2ºA', 'postal_code' => '23001', 'provincia' => 'Jaén'],
            ],
            // 7. Ghana - Hombre casado
            [
                'foreigner' => [
                    'first_name' => 'Kwame',
                    'last_name' => 'Mensah',
                    'passport' => 'GH67890001',
                    'nie' => 'X5678901G',
                    'niss' => '281200000107',
                    'gender' => Gender::H->value,
                    'birthdate' => '1986-09-14',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'GH',
                    'birthplace_name' => 'Kumasi',
                ],
                'extra' => [
                    'father_name' => 'Kofi Mensah',
                    'mother_name' => 'Ama Asante',
                    'phone' => '656001001',
                    'email' => 'kwame.mensah@email.com',
                ],
                'address' => ['street_name' => 'Calle Mayor', 'number' => '17', 'floor_door' => '1ºB', 'postal_code' => '30001', 'provincia' => 'Murcia'],
            ],
            // 8. Camerún - Mujer soltera
            [
                'foreigner' => [
                    'first_name' => 'Ange',
                    'last_name' => 'Mbarga Eyenga',
                    'passport' => 'CM78901001',
                    'nie' => 'X6789012H',
                    'niss' => '281200000108',
                    'gender' => Gender::M->value,
                    'birthdate' => '1996-05-30',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'CM',
                    'birthplace_name' => 'Douala',
                ],
                'extra' => [
                    'father_name' => 'Jean-Pierre Mbarga',
                    'mother_name' => 'Marie-Claire Eyenga',
                    'phone' => '667001001',
                    'email' => 'ange.mbarga@email.com',
                ],
                'address' => ['street_name' => 'Avda. de la Libertad', 'number' => '60', 'floor_door' => '5ºA', 'postal_code' => '30008', 'provincia' => 'Murcia'],
            ],
            // 9. Costa de Marfil - Hombre soltero
            [
                'foreigner' => [
                    'first_name' => 'Aboubacar',
                    'last_name' => 'Koné',
                    'passport' => 'CI89012001',
                    'nie' => 'X7890123J',
                    'niss' => '281200000109',
                    'gender' => Gender::H->value,
                    'birthdate' => '1992-11-07',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'CI',
                    'birthplace_name' => 'Bouaké',
                ],
                'extra' => [
                    'father_name' => 'Drissa Koné',
                    'mother_name' => 'Aminata Touré',
                    'phone' => '678001001',
                    'email' => 'aboubacar.kone@email.com',
                ],
                'address' => ['street_name' => 'Carrer Major', 'number' => '33', 'floor_door' => '2ºC', 'postal_code' => '25002', 'provincia' => 'Lleida'],
            ],
            // 10. Guinea Conakry - Hombre casado
            [
                'foreigner' => [
                    'first_name' => 'Alpha',
                    'last_name' => 'Camara',
                    'passport' => 'GN90123001',
                    'nie' => 'X8901234K',
                    'niss' => '281200000110',
                    'gender' => Gender::H->value,
                    'birthdate' => '1984-07-22',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'GN',
                    'birthplace_name' => 'Conakry',
                ],
                'extra' => [
                    'father_name' => 'Mamadou Camara',
                    'mother_name' => 'Aissatou Barry',
                    'phone' => '689001001',
                    'email' => 'alpha.camara@email.com',
                ],
                'address' => ['street_name' => 'Avda. Andalucía', 'number' => '12', 'floor_door' => '3ºB', 'postal_code' => '25003', 'provincia' => 'Lleida'],
            ],
            // 11. Mauritania - Hombre soltero
            [
                'foreigner' => [
                    'first_name' => 'Mohamed',
                    'last_name' => 'Ould Sidi',
                    'passport' => 'MR01234001',
                    'nie' => 'X9012345L',
                    'niss' => '281200000111',
                    'gender' => Gender::H->value,
                    'birthdate' => '1990-02-15',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'MR',
                    'birthplace_name' => 'Nouakchott',
                ],
                'extra' => [
                    'father_name' => 'Sidi Mohamed Ould Ahmed',
                    'mother_name' => 'Mariem Mint Abdallahi',
                    'phone' => '690001001',
                    'email' => 'mohamed.ouldsidi@email.com',
                ],
                'address' => ['street_name' => 'Calle San Fernando', 'number' => '9', 'floor_door' => null, 'postal_code' => '21003', 'provincia' => 'Huelva'],
            ],
            // 12. Gambia - Hombre soltero
            [
                'foreigner' => [
                    'first_name' => 'Lamin',
                    'last_name' => 'Jammeh',
                    'passport' => 'GM12345001',
                    'nie' => 'X0123456M',
                    'niss' => '281200000112',
                    'gender' => Gender::H->value,
                    'birthdate' => '1997-10-09',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'GM',
                    'birthplace_name' => 'Banjul',
                ],
                'extra' => [
                    'father_name' => 'Ebrima Jammeh',
                    'mother_name' => 'Isatou Ceesay',
                    'phone' => '601001001',
                    'email' => 'lamin.jammeh@email.com',
                ],
                'address' => ['street_name' => 'Calle Puerta de Purchena', 'number' => '3', 'floor_door' => '1ºA', 'postal_code' => '04001', 'provincia' => 'Almería'],
            ],
            // 13. Marruecos - Hombre soltero joven
            [
                'foreigner' => [
                    'first_name' => 'Omar',
                    'last_name' => 'Ait Brahim',
                    'passport' => 'MA23456003',
                    'nie' => 'Y4567890N',
                    'niss' => '281200000113',
                    'gender' => Gender::H->value,
                    'birthdate' => '1998-01-20',
                    'marital_status' => MaritalStatus::Sol->value,
                    'nationality_iso' => 'MA',
                    'birthplace_name' => 'Nador',
                ],
                'extra' => [
                    'father_name' => 'Rachid Ait Brahim',
                    'mother_name' => 'Zohra Berrada',
                    'phone' => '612002001',
                    'email' => 'omar.aitbrahim@email.com',
                ],
                'address' => ['street_name' => 'Plaza de las Flores', 'number' => '7', 'floor_door' => '4ºB', 'postal_code' => '30004', 'provincia' => 'Murcia'],
            ],
            // 14. Nigeria - Hombre casado
            [
                'foreigner' => [
                    'first_name' => 'Chinedu',
                    'last_name' => 'Obi',
                    'passport' => 'NG45678002',
                    'nie' => 'Y5678901P',
                    'niss' => '281200000114',
                    'gender' => Gender::H->value,
                    'birthdate' => '1988-04-05',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'NG',
                    'birthplace_name' => 'Enugu',
                ],
                'extra' => [
                    'father_name' => 'Emeka Obi',
                    'mother_name' => 'Nneka Obi',
                    'phone' => '634002001',
                    'email' => 'chinedu.obi@email.com',
                ],
                'address' => ['street_name' => 'Calle Mesones', 'number' => '19', 'floor_door' => '2ºD', 'postal_code' => '23002', 'provincia' => 'Jaén'],
            ],
            // 15. Senegal - Mujer casada
            [
                'foreigner' => [
                    'first_name' => 'Aïssatou',
                    'last_name' => 'Fall',
                    'passport' => 'SN34567003',
                    'nie' => 'Y6789012Q',
                    'niss' => '281200000115',
                    'gender' => Gender::M->value,
                    'birthdate' => '1995-09-12',
                    'marital_status' => MaritalStatus::Cas->value,
                    'nationality_iso' => 'SN',
                    'birthplace_name' => 'Kaolack',
                ],
                'extra' => [
                    'father_name' => 'Cheikh Fall',
                    'mother_name' => 'Coumba Dieng',
                    'phone' => '623002001',
                    'email' => 'aissatou.fall@email.com',
                ],
                'address' => ['street_name' => 'Calle Plus Ultra', 'number' => '26', 'floor_door' => '1ºC', 'postal_code' => '21004', 'provincia' => 'Huelva'],
            ],
        ];

        $created = 0;

        foreach ($foreigners as $data) {
            $iso = $data['foreigner']['nationality_iso'];

            if (! $countries->has($iso)) {
                continue;
            }

            $country = $countries->get($iso);

            $foreignerData = collect($data['foreigner'])
                ->except('nationality_iso')
                ->merge([
                    'nationality_id' => $country->id,
                    'birth_country_id' => $country->id,
                ])
                ->toArray();

            $foreigner = Foreigner::create($foreignerData);

            ForeignerExtraData::create(array_merge(
                ['foreigner_id' => $foreigner->id],
                $data['extra']
            ));

            // Dirección de residencia en España
            if (isset($data['address']) && $spain) {
                $addr = $data['address'];
                $prov = $provinces->get($addr['provincia']);

                Address::create([
                    'addressable_type' => Foreigner::class,
                    'addressable_id' => $foreigner->id,
                    'street_name' => $addr['street_name'],
                    'number' => $addr['number'],
                    'floor_door' => $addr['floor_door'],
                    'postal_code' => $addr['postal_code'],
                    'province_id' => $prov['province']?->id,
                    'municipality_id' => $prov['municipality']?->id,
                    'country_id' => $spain->id,
                ]);
            }

            $created++;
        }

        $this->command->info("Creados {$created} trabajadores extranjeros.");
    }
}
