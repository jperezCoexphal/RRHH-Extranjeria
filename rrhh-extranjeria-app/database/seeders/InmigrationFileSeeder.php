<?php

namespace Database\Seeders;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\WorkingDayType;
use App\Models\Address;
use App\Models\Country;
use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\InmigrationFile;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Seeder;

class InmigrationFileSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando expedientes de extranjería...');

        $editor = User::first();

        if (! $editor) {
            $this->command->error('No se encontró ningún usuario. Ejecuta UserSeeder primero.');
            return;
        }

        $employers = Employer::orderBy('id')->get();
        $foreigners = Foreigner::orderBy('id')->get();

        if ($employers->count() < 10 || $foreigners->count() < 15) {
            $this->command->error("Se necesitan al menos 10 empleadores y 15 trabajadores. Encontrados: {$employers->count()} empleadores, {$foreigners->count()} trabajadores.");
            return;
        }

        $spain = Country::where('iso_code_2', 'ES')->first();

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

        $campaign = '2025-2026';
        $campaignYears = explode('-', $campaign);
        $shortCampaign = substr($campaignYears[0], -2) . substr($campaignYears[1], -2);
        $sequence = InmigrationFile::where('campaign', $campaign)->count();

        $files = [
            // 1. Youssef Belhaj → Agroberries Sur — Cuenta ajena, Borrador
            [
                'foreigner_idx' => 0,
                'employer_idx' => 0,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::BORRADOR,
                'job_title' => 'Peón Agrícola',
                'start_date' => '2026-03-01',
                'end_date' => null,
                'salary' => 18900.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Finca Las Fresas',
                    'number' => 's/n',
                    'postal_code' => '21730',
                    'province' => $huelva,
                    'municipality' => $munHuelva,
                ],
            ],
            // 2. Naima Ouazzani → Hortalizas del Poniente — Temporada, Pendiente revisión
            [
                'foreigner_idx' => 1,
                'employer_idx' => 1,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::PENDIENTE_REVISION,
                'job_title' => 'Recolectora de Hortalizas',
                'start_date' => '2026-04-01',
                'end_date' => '2026-09-30',
                'salary' => 16800.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 15,
                'work_address' => [
                    'street_name' => 'Paraje Los Invernaderos',
                    'number' => '3',
                    'floor_door' => 'Nave 2',
                    'postal_code' => '04700',
                    'province' => $almeria,
                    'municipality' => $munAlmeria,
                ],
            ],
            // 3. Ibrahima Ndiaye → Olivos del Guadalquivir — Cuenta ajena, Listo
            [
                'foreigner_idx' => 2,
                'employer_idx' => 2,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::LISTO,
                'job_title' => 'Operario de Almazara',
                'start_date' => '2026-01-15',
                'end_date' => null,
                'salary' => 19600.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Ctra. de la Almazara',
                    'number' => 'km 4',
                    'postal_code' => '23600',
                    'province' => $jaen,
                    'municipality' => $munJaen,
                ],
            ],
            // 4. Mamadou Ba → Explotaciones Navarro — Temporada, Presentado
            [
                'foreigner_idx' => 3,
                'employer_idx' => 3,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::PRESENTADO,
                'job_title' => 'Peón de Frutales',
                'start_date' => '2026-05-01',
                'end_date' => '2026-10-31',
                'salary' => 16520.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 15,
                'work_address' => [
                    'street_name' => 'Camino de la Huerta',
                    'number' => '3',
                    'floor_door' => 'Parcela 12',
                    'postal_code' => '30820',
                    'province' => $murcia,
                    'municipality' => $munMurcia,
                ],
            ],
            // 5. Blessing Adeyemi → Fruita del Segrià — Arraigo, Requerido
            [
                'foreigner_idx' => 4,
                'employer_idx' => 4,
                'application_type' => ApplicationType::EX_10,
                'status' => ImmigrationFileStatus::REQUERIDO,
                'job_title' => 'Auxiliar de Almacén',
                'start_date' => '2026-02-01',
                'end_date' => null,
                'salary' => 17500.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Polígon Agro-Segrià',
                    'number' => '22',
                    'floor_door' => 'Nave Almacén',
                    'postal_code' => '25200',
                    'province' => $lleida,
                    'municipality' => $munLleida,
                ],
            ],
            // 6. Seydou Traoré → SAT Campos de Niebla — Cuenta ajena, Favorable
            [
                'foreigner_idx' => 5,
                'employer_idx' => 5,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::FAVORABLE,
                'job_title' => 'Podador Especializado',
                'start_date' => '2025-11-01',
                'end_date' => null,
                'salary' => 21000.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Finca El Alcornocal',
                    'number' => 's/n',
                    'floor_door' => 'Parcela Norte',
                    'postal_code' => '21640',
                    'province' => $huelva,
                    'municipality' => $munHuelva,
                ],
            ],
            // 7. Kwame Mensah → BioAlmería Cultivos — Temporada, Denegado
            [
                'foreigner_idx' => 6,
                'employer_idx' => 6,
                'application_type' => ApplicationType::EX_26,
                'status' => ImmigrationFileStatus::DENEGADO,
                'job_title' => 'Peón de Invernadero',
                'start_date' => '2026-03-15',
                'end_date' => '2026-08-15',
                'salary' => 16100.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 15,
                'work_address' => [
                    'street_name' => 'Paraje Las Norias',
                    'number' => '7',
                    'floor_door' => 'Invernadero 3',
                    'postal_code' => '04740',
                    'province' => $almeria,
                    'municipality' => $munAlmeria,
                ],
            ],
            // 8. Ange Mbarga → Ganadería Ortega — Cuenta ajena, Borrador
            [
                'foreigner_idx' => 7,
                'employer_idx' => 7,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::BORRADOR,
                'job_title' => 'Pastora de Ganado',
                'start_date' => '2026-04-01',
                'end_date' => null,
                'salary' => 18200.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Cortijo El Encinar',
                    'number' => 's/n',
                    'postal_code' => '23470',
                    'province' => $jaen,
                    'municipality' => $munJaen,
                ],
            ],
            // 9. Aboubacar Koné → Cítricos del Segura — Temporada, Presentado
            [
                'foreigner_idx' => 8,
                'employer_idx' => 8,
                'application_type' => ApplicationType::EX_10,
                'status' => ImmigrationFileStatus::PRESENTADO,
                'job_title' => 'Recolector de Cítricos',
                'start_date' => '2026-01-10',
                'end_date' => '2026-06-30',
                'salary' => 16800.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 15,
                'work_address' => [
                    'street_name' => 'Finca Los Limoneros',
                    'number' => 's/n',
                    'postal_code' => '30530',
                    'province' => $murcia,
                    'municipality' => $munMurcia,
                ],
            ],
            // 10. Alpha Camara → Vivers Plana — Cuenta ajena, Listo
            [
                'foreigner_idx' => 9,
                'employer_idx' => 9,
                'application_type' => ApplicationType::EX_10,
                'status' => ImmigrationFileStatus::LISTO,
                'job_title' => 'Viverista',
                'start_date' => '2026-03-01',
                'end_date' => null,
                'salary' => 19320.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Camí de Corbins',
                    'number' => '15',
                    'floor_door' => 'Vivero Principal',
                    'postal_code' => '25130',
                    'province' => $lleida,
                    'municipality' => $munLleida,
                ],
            ],
            // 11. Mohamed Ould Sidi → Agroberries Sur — Temporada, Pendiente revisión
            [
                'foreigner_idx' => 10,
                'employer_idx' => 0,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::PENDIENTE_REVISION,
                'job_title' => 'Recolector de Fresas',
                'start_date' => '2026-02-15',
                'end_date' => '2026-07-15',
                'salary' => 16520.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 15,
                'work_address' => [
                    'street_name' => 'Finca Las Fresas',
                    'number' => 's/n',
                    'floor_door' => 'Sector B',
                    'postal_code' => '21730',
                    'province' => $huelva,
                    'municipality' => $munHuelva,
                ],
            ],
            // 12. Lamin Jammeh → Hortalizas del Poniente — Cuenta ajena parcial, Borrador
            [
                'foreigner_idx' => 11,
                'employer_idx' => 1,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::BORRADOR,
                'job_title' => 'Auxiliar de Riego',
                'start_date' => '2026-05-01',
                'end_date' => null,
                'salary' => 12600.00,
                'working_day_type' => WorkingDayType::PARCIAL,
                'working_hours' => 25,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Paraje Los Invernaderos',
                    'number' => '3',
                    'floor_door' => 'Zona de Riego',
                    'postal_code' => '04700',
                    'province' => $almeria,
                    'municipality' => $munAlmeria,
                ],
            ],
            // 13. Omar Ait Brahim → Olivos del Guadalquivir — Temporada, Favorable
            [
                'foreigner_idx' => 12,
                'employer_idx' => 2,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::FAVORABLE,
                'job_title' => 'Vareador de Olivos',
                'start_date' => '2025-10-15',
                'end_date' => '2026-02-28',
                'salary' => 17500.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 15,
                'work_address' => [
                    'street_name' => 'Finca Olivar de la Sierra',
                    'number' => 's/n',
                    'postal_code' => '23600',
                    'province' => $jaen,
                    'municipality' => $munJaen,
                ],
            ],
            // 14. Chinedu Obi → SAT Campos de Niebla — Arraigo, Presentado
            [
                'foreigner_idx' => 13,
                'employer_idx' => 5,
                'application_type' => ApplicationType::EX_10,
                'status' => ImmigrationFileStatus::PRESENTADO,
                'job_title' => 'Tractorista',
                'start_date' => '2026-01-01',
                'end_date' => null,
                'salary' => 20300.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Finca El Alcornocal',
                    'number' => 's/n',
                    'floor_door' => 'Parcela Sur',
                    'postal_code' => '21640',
                    'province' => $huelva,
                    'municipality' => $munHuelva,
                ],
            ],
            // 15. Aïssatou Fall → BioAlmería Cultivos — Cuenta ajena, Borrador
            [
                'foreigner_idx' => 14,
                'employer_idx' => 6,
                'application_type' => ApplicationType::EX_03,
                'status' => ImmigrationFileStatus::BORRADOR,
                'job_title' => 'Operaria de Envasado',
                'start_date' => '2026-06-01',
                'end_date' => null,
                'salary' => 17920.00,
                'working_day_type' => WorkingDayType::COMPLETA,
                'working_hours' => 40,
                'probation_period' => 30,
                'work_address' => [
                    'street_name' => 'Paraje Las Norias',
                    'number' => '7',
                    'floor_door' => 'Nave Envasado',
                    'postal_code' => '04740',
                    'province' => $almeria,
                    'municipality' => $munAlmeria,
                ],
            ],
        ];

        $created = 0;

        foreach ($files as $data) {
            $foreigner = $foreigners[$data['foreigner_idx']];
            $employer = $employers[$data['employer_idx']];

            $sequence++;
            $code = sprintf('EXP-%s-%04d', $shortCampaign, $sequence);

            $title = sprintf(
                '%s %s - %s',
                $foreigner->first_name,
                $foreigner->last_name,
                $employer->comercial_name ?? $employer->fiscal_name
            );

            $file = InmigrationFile::create([
                'campaign' => $campaign,
                'file_code' => $code,
                'file_title' => $title,
                'application_type' => $data['application_type']->value,
                'status' => $data['status']->value,
                'job_title' => $data['job_title'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'salary' => $data['salary'],
                'working_day_type' => $data['working_day_type']->value,
                'working_hours' => $data['working_hours'],
                'probation_period' => $data['probation_period'],
                'editor_id' => $editor->id,
                'employer_id' => $employer->id,
                'foreigner_id' => $foreigner->id,
            ]);

            $addr = $data['work_address'];
            Address::create([
                'addressable_type' => InmigrationFile::class,
                'addressable_id' => $file->id,
                'street_name' => $addr['street_name'],
                'number' => $addr['number'],
                'floor_door' => $addr['floor_door'] ?? null,
                'postal_code' => $addr['postal_code'],
                'province_id' => $addr['province']?->id,
                'municipality_id' => $addr['municipality']?->id,
                'country_id' => $spain->id,
            ]);

            $created++;
        }

        $this->command->info("Creados {$created} expedientes de extranjería.");
    }
}
