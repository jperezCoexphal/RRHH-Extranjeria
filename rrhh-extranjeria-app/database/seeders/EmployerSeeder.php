<?php

namespace Database\Seeders;

use App\Enums\LegalForm;
use App\Models\Address;
use App\Models\Company;
use App\Models\Country;
use App\Models\Employer;
use App\Models\Freelancer;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Database\Seeder;

class EmployerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando empleadores...');

        $spain = Country::where('iso_code_2', 'ES')->first();

        if (! $spain) {
            $this->command->error('No se encontró España. Ejecuta CountrySeeder primero.');
            return;
        }

        // Provincias agrícolas clave
        $huelva = Province::where('province_name', 'Huelva')->first();
        $almeria = Province::where('province_name', 'Almería')->first();
        $jaen = Province::where('province_name', 'Jaén')->first();
        $murcia = Province::where('province_name', 'Murcia')->first();
        $lleida = Province::where('province_name', 'Lleida')->first();

        $munHuelva = Municipality::where('province_id', $huelva?->id)->first();
        $munAlmeria = Municipality::where('province_id', $almeria?->id)->first();
        $munJaen = Municipality::where('province_id', $jaen?->id)->first();
        $munMurcia = Municipality::where('province_id', $murcia?->id)->first();
        $munLleida = Municipality::where('province_id', $lleida?->id)->first();

        $employers = [
            // 1. SL - Fresas Huelva
            [
                'employer' => [
                    'legal_form' => LegalForm::SL->value,
                    'comercial_name' => 'Agroberries Sur',
                    'fiscal_name' => 'Agroberries Sur SL',
                    'nif' => 'B21334455',
                    'ccc' => '21100112233',
                    'cnae' => '0113',
                    'email' => 'rrhh@agroberriessur.es',
                    'phone' => '959 220 110',
                    'is_associated' => true,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Francisco Romero Caballero',
                    'representative_title' => 'Administrador Único',
                    'representantive_identity_number' => '29876543D',
                ],
                'address' => [
                    'street_name' => 'Ctra. de Almonte',
                    'number' => '12',
                    'postal_code' => '21730',
                    'province' => $huelva,
                    'municipality' => $munHuelva,
                ],
            ],
            // 2. SA - Gran explotación Almería
            [
                'employer' => [
                    'legal_form' => LegalForm::SA->value,
                    'comercial_name' => 'Hortalizas del Poniente',
                    'fiscal_name' => 'Hortalizas del Poniente SA',
                    'nif' => 'A04567812',
                    'ccc' => '04200334455',
                    'cnae' => '0113',
                    'email' => 'personal@hortponiente.com',
                    'phone' => '950 401 200',
                    'is_associated' => true,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Isabel Martínez Sánchez',
                    'representative_title' => 'Directora General',
                    'representantive_identity_number' => '75432198E',
                ],
                'address' => [
                    'street_name' => 'Paraje Los Invernaderos',
                    'number' => '1',
                    'floor_door' => 'Nave 4',
                    'postal_code' => '04700',
                    'province' => $almeria,
                    'municipality' => $munAlmeria,
                ],
            ],
            // 3. COOP - Cooperativa olivar Jaén
            [
                'employer' => [
                    'legal_form' => LegalForm::COOP->value,
                    'comercial_name' => 'Olivos del Guadalquivir',
                    'fiscal_name' => 'Sociedad Cooperativa Olivos del Guadalquivir',
                    'nif' => 'F23112233',
                    'ccc' => '23300556677',
                    'cnae' => '0126',
                    'email' => 'administracion@olivosguadalquivir.coop',
                    'phone' => '953 310 450',
                    'is_associated' => true,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Manuel López Herrera',
                    'representative_title' => 'Presidente',
                    'representantive_identity_number' => '26789012F',
                ],
                'address' => [
                    'street_name' => 'Avda. del Olivo',
                    'number' => '56',
                    'postal_code' => '23600',
                    'province' => $jaen,
                    'municipality' => $munJaen,
                ],
            ],
            // 4. EI - Autónomo Murcia
            [
                'employer' => [
                    'legal_form' => LegalForm::EI->value,
                    'comercial_name' => 'Explotaciones Navarro',
                    'fiscal_name' => 'Pedro Navarro Giménez',
                    'nif' => '48123456G',
                    'ccc' => '30400778899',
                    'cnae' => '0124',
                    'email' => 'pnavarro@explotacionesnavarro.es',
                    'phone' => '968 112 334',
                    'is_associated' => false,
                ],
                'type' => 'freelancer',
                'freelancer' => [
                    'first_name' => 'Pedro',
                    'last_name' => 'Navarro Giménez',
                    'niss' => '300412345678',
                    'birthdate' => '1972-09-03',
                ],
                'address' => [
                    'street_name' => 'Camino de la Huerta',
                    'number' => '3',
                    'postal_code' => '30820',
                    'province' => $murcia,
                    'municipality' => $munMurcia,
                ],
            ],
            // 5. SL - Empresa frutícola Lleida
            [
                'employer' => [
                    'legal_form' => LegalForm::SL->value,
                    'comercial_name' => 'Fruita del Segrià',
                    'fiscal_name' => 'Fruita del Segrià SL',
                    'nif' => 'B25998877',
                    'ccc' => '25500990011',
                    'cnae' => '0124',
                    'email' => 'info@fruitasegria.cat',
                    'phone' => '973 280 560',
                    'is_associated' => true,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Jordi Puig Solé',
                    'representative_title' => 'Administrador Solidario',
                    'representantive_identity_number' => '40567890H',
                ],
                'address' => [
                    'street_name' => 'Polígon Agro-Segrià',
                    'number' => '22',
                    'floor_door' => 'Parcela 8',
                    'postal_code' => '25200',
                    'province' => $lleida,
                    'municipality' => $munLleida,
                ],
            ],
            // 6. SAT - Sociedad Agraria Huelva
            [
                'employer' => [
                    'legal_form' => LegalForm::SAT->value,
                    'comercial_name' => 'SAT Campos de Niebla',
                    'fiscal_name' => 'Sociedad Agraria de Transformación Campos de Niebla',
                    'nif' => 'V21667788',
                    'ccc' => '21100223344',
                    'cnae' => '0125',
                    'email' => 'sat@camposdeniebla.es',
                    'phone' => '959 445 667',
                    'is_associated' => true,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Carmen Díaz Vega',
                    'representative_title' => 'Presidenta',
                    'representantive_identity_number' => '29345678J',
                ],
                'address' => [
                    'street_name' => 'Finca El Alcornocal',
                    'number' => 's/n',
                    'postal_code' => '21640',
                    'province' => $huelva,
                    'municipality' => $munHuelva,
                ],
            ],
            // 7. SL - Invernaderos Almería
            [
                'employer' => [
                    'legal_form' => LegalForm::SL->value,
                    'comercial_name' => 'BioAlmería Cultivos',
                    'fiscal_name' => 'BioAlmería Cultivos Ecológicos SL',
                    'nif' => 'B04223344',
                    'ccc' => '04200556677',
                    'cnae' => '0119',
                    'email' => 'contacto@bioalmeria.es',
                    'phone' => '950 302 115',
                    'is_associated' => false,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Ana Belén Ruiz Torres',
                    'representative_title' => 'Administradora',
                    'representantive_identity_number' => '75678901K',
                ],
                'address' => [
                    'street_name' => 'Paraje Las Norias',
                    'number' => '7',
                    'postal_code' => '04740',
                    'province' => $almeria,
                    'municipality' => $munAlmeria,
                ],
            ],
            // 8. EI - Autónomo ganadero Jaén
            [
                'employer' => [
                    'legal_form' => LegalForm::EI->value,
                    'comercial_name' => 'Ganadería Ortega',
                    'fiscal_name' => 'Luis Ortega Muñoz',
                    'nif' => '26890123L',
                    'ccc' => '23300889900',
                    'cnae' => '0145',
                    'email' => 'lortega@ganaderiaortega.es',
                    'phone' => '953 221 780',
                    'is_associated' => false,
                ],
                'type' => 'freelancer',
                'freelancer' => [
                    'first_name' => 'Luis',
                    'last_name' => 'Ortega Muñoz',
                    'niss' => '230498765432',
                    'birthdate' => '1968-02-18',
                ],
                'address' => [
                    'street_name' => 'Cortijo El Encinar',
                    'number' => 's/n',
                    'postal_code' => '23470',
                    'province' => $jaen,
                    'municipality' => $munJaen,
                ],
            ],
            // 9. COOP - Cooperativa citrícola Murcia
            [
                'employer' => [
                    'legal_form' => LegalForm::COOP->value,
                    'comercial_name' => 'Cítricos del Segura',
                    'fiscal_name' => 'Cooperativa Agrícola Cítricos del Segura',
                    'nif' => 'F30445566',
                    'ccc' => '30400112244',
                    'cnae' => '0123',
                    'email' => 'socios@citricosdelsegura.coop',
                    'phone' => '968 334 556',
                    'is_associated' => true,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Josefa Hernández Cano',
                    'representative_title' => 'Secretaria General',
                    'representantive_identity_number' => '48234567M',
                ],
                'address' => [
                    'street_name' => 'Avda. de la Vega',
                    'number' => '90',
                    'postal_code' => '30530',
                    'province' => $murcia,
                    'municipality' => $munMurcia,
                ],
            ],
            // 10. SL - Viveros Lleida
            [
                'employer' => [
                    'legal_form' => LegalForm::SL->value,
                    'comercial_name' => 'Vivers Plana',
                    'fiscal_name' => 'Vivers Plana SL',
                    'nif' => 'B25776655',
                    'ccc' => '25500334466',
                    'cnae' => '0130',
                    'email' => 'oficina@viversplana.cat',
                    'phone' => '973 150 230',
                    'is_associated' => false,
                ],
                'type' => 'company',
                'company' => [
                    'representative_name' => 'Marc Plana Ferrer',
                    'representative_title' => 'Administrador Único',
                    'representantive_identity_number' => '40890123N',
                ],
                'address' => [
                    'street_name' => 'Camí de Corbins',
                    'number' => '15',
                    'postal_code' => '25130',
                    'province' => $lleida,
                    'municipality' => $munLleida,
                ],
            ],
        ];

        foreach ($employers as $data) {
            $employer = Employer::create($data['employer']);

            if ($data['type'] === 'company') {
                Company::create(array_merge(
                    ['employer_id' => $employer->id],
                    $data['company']
                ));
            } else {
                Freelancer::create(array_merge(
                    ['employer_id' => $employer->id],
                    $data['freelancer']
                ));
            }

            $addr = $data['address'];
            Address::create([
                'addressable_type' => Employer::class,
                'addressable_id' => $employer->id,
                'street_name' => $addr['street_name'],
                'number' => $addr['number'],
                'floor_door' => $addr['floor_door'] ?? null,
                'postal_code' => $addr['postal_code'],
                'province_id' => $addr['province']?->id,
                'municipality_id' => $addr['municipality']?->id,
                'country_id' => $spain->id,
            ]);
        }

        $this->command->info('Creados 10 empleadores.');
    }
}
