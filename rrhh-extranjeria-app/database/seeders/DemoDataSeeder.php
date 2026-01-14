<?php

namespace Database\Seeders;

use App\Enums\ApplicationType;
use App\Enums\Gender;
use App\Enums\ImmigrationFileStatus;
use App\Enums\LegalForm;
use App\Enums\MaritalStatus;
use App\Enums\WorkingDayType;
use App\Models\Address;
use App\Models\Company;
use App\Models\Country;
use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\ForeignerExtraData;
use App\Models\InmigrationFile;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo data: 3 employers, 3 foreigners (African), 3 immigration files
     */
    public function run(): void
    {
        $this->command->info('Creating demo data...');

        // Get necessary references
        $spain = Country::where('iso_code_2', 'ES')->first();
        $morocco = Country::where('iso_code_2', 'MA')->first();
        $senegal = Country::where('iso_code_2', 'SN')->first();
        $nigeria = Country::where('iso_code_2', 'NG')->first();

        $huelva = Province::where('province_name', 'Huelva')->first();
        $municipality = Municipality::where('province_id', $huelva?->id)->first();

        $editor = User::first();

        if (!$spain || !$morocco || !$senegal || !$nigeria) {
            $this->command->error('Countries not found. Run CountrySeeder first.');
            return;
        }

        if (!$editor) {
            $this->command->error('No user found. Run UserSeeder first.');
            return;
        }

        // =====================================================================
        // EMPLOYERS
        // =====================================================================

        // Employer 1: Empresa agrícola (SL)
        $employer1 = Employer::create([
            'legal_form' => LegalForm::SL->value,
            'comercial_name' => 'Fresas del Condado',
            'fiscal_name' => 'Fresas del Condado SL',
            'nif' => 'B21456789',
            'ccc' => '21123456789',
            'cnae' => '0113',
            'email' => 'info@fresasdelcondado.es',
            'phone' => '959123456',
            'is_associated' => true,
        ]);

        Company::create([
            'employer_id' => $employer1->id,
            'representative_name' => 'Antonio García López',
            'representative_title' => 'Administrador',
            'representantive_identity_number' => '28456789A',
        ]);

        $this->createAddress($employer1, $spain, $huelva, $municipality, [
            'street_name' => 'Calle de la Fresa',
            'number' => '15',
            'postal_code' => '21440',
        ]);

        // Employer 2: Cooperativa agrícola
        $employer2 = Employer::create([
            'legal_form' => LegalForm::COOP->value,
            'comercial_name' => 'Cítricos de Huelva',
            'fiscal_name' => 'Cooperativa Cítricos de Huelva',
            'nif' => 'F21789456',
            'ccc' => '21987654321',
            'cnae' => '0124',
            'email' => 'contacto@citricosdehuelva.coop',
            'phone' => '959654321',
            'is_associated' => true,
        ]);

        Company::create([
            'employer_id' => $employer2->id,
            'representative_name' => 'María Fernández Ruiz',
            'representative_title' => 'Presidenta',
            'representantive_identity_number' => '29123456B',
        ]);

        $this->createAddress($employer2, $spain, $huelva, $municipality, [
            'street_name' => 'Avenida de los Naranjos',
            'number' => '42',
            'postal_code' => '21450',
        ]);

        // Employer 3: Autónomo (EI)
        $employer3 = Employer::create([
            'legal_form' => LegalForm::EI->value,
            'comercial_name' => 'Invernaderos Pérez',
            'fiscal_name' => 'José Pérez Martín',
            'nif' => '30456789C',
            'ccc' => '21456789012',
            'cnae' => '0119',
            'email' => 'jperez@invernaderosperez.com',
            'phone' => '959789012',
            'is_associated' => false,
        ]);

        // For EI (autónomo), create freelancer instead of company
        \App\Models\Freelancer::create([
            'employer_id' => $employer3->id,
            'first_name' => 'José',
            'last_name' => 'Pérez Martín',
            'niss' => '211234567890',
            'birthdate' => '1975-06-15',
        ]);

        $this->createAddress($employer3, $spain, $huelva, $municipality, [
            'street_name' => 'Camino del Invernadero',
            'number' => '8',
            'postal_code' => '21460',
        ]);

        $this->command->info('Created 3 employers.');

        // =====================================================================
        // FOREIGNERS (African countries)
        // =====================================================================

        // Foreigner 1: Moroccan worker
        $foreigner1 = Foreigner::create([
            'first_name' => 'Ahmed',
            'last_name' => 'El Amrani',
            'passport' => 'MA12345678',
            'nie' => 'Y1234567A',
            'niss' => '281234567801',
            'gender' => Gender::H->value,
            'birthdate' => '1990-03-15',
            'marital_status' => MaritalStatus::Cas->value,
            'nationality_id' => $morocco->id,
            'birth_country_id' => $morocco->id,
            'birthplace_name' => 'Casablanca',
        ]);

        ForeignerExtraData::create([
            'foreigner_id' => $foreigner1->id,
            'father_name' => 'Mohammed El Amrani',
            'mother_name' => 'Fatima Benali',
            'phone' => '612345678',
            'email' => 'ahmed.elamrani@email.com',
        ]);

        // Foreigner 2: Senegalese worker
        $foreigner2 = Foreigner::create([
            'first_name' => 'Moussa',
            'last_name' => 'Diallo',
            'passport' => 'SN87654321',
            'nie' => 'Y2345678B',
            'niss' => '281234567802',
            'gender' => Gender::H->value,
            'birthdate' => '1988-07-22',
            'marital_status' => MaritalStatus::Sol->value,
            'nationality_id' => $senegal->id,
            'birth_country_id' => $senegal->id,
            'birthplace_name' => 'Dakar',
        ]);

        ForeignerExtraData::create([
            'foreigner_id' => $foreigner2->id,
            'father_name' => 'Ibrahima Diallo',
            'mother_name' => 'Aminata Sow',
            'phone' => '623456789',
            'email' => 'moussa.diallo@email.com',
        ]);

        // Foreigner 3: Nigerian worker
        $foreigner3 = Foreigner::create([
            'first_name' => 'Chioma',
            'last_name' => 'Okafor',
            'passport' => 'NG11223344',
            'nie' => 'Y3456789C',
            'niss' => '281234567803',
            'gender' => Gender::M->value,
            'birthdate' => '1995-11-08',
            'marital_status' => MaritalStatus::Sol->value,
            'nationality_id' => $nigeria->id,
            'birth_country_id' => $nigeria->id,
            'birthplace_name' => 'Lagos',
        ]);

        ForeignerExtraData::create([
            'foreigner_id' => $foreigner3->id,
            'father_name' => 'Emmanuel Okafor',
            'mother_name' => 'Ngozi Okafor',
            'phone' => '634567890',
            'email' => 'chioma.okafor@email.com',
        ]);

        $this->command->info('Created 3 foreigners (Morocco, Senegal, Nigeria).');

        // =====================================================================
        // IMMIGRATION FILES
        // =====================================================================

        // File 1: Ahmed + Fresas del Condado - Temporada
        $file1 = InmigrationFile::create([
            'campaign' => '2025-2026',
            'file_code' => 'EXP-2025-001',
            'file_title' => 'Ahmed El Amrani - Fresas del Condado SL',
            'application_type' => ApplicationType::EX_04->value,
            'status' => ImmigrationFileStatus::BORRADOR->value,
            'job_title' => 'Peón Agrícola',
            'start_date' => '2025-02-01',
            'end_date' => '2025-06-30',
            'salary' => 1200.00,
            'working_day_type' => WorkingDayType::COMPLETA->value,
            'working_hours' => 40,
            'probation_period' => 15,
            'editor_id' => $editor->id,
            'employer_id' => $employer1->id,
            'foreigner_id' => $foreigner1->id,
        ]);

        // File 2: Moussa + Cítricos de Huelva - Cuenta ajena
        $file2 = InmigrationFile::create([
            'campaign' => '2025-2026',
            'file_code' => 'EXP-2025-002',
            'file_title' => 'Moussa Diallo - Coop. Cítricos de Huelva',
            'application_type' => ApplicationType::EX_03->value,
            'status' => ImmigrationFileStatus::LISTO->value,
            'job_title' => 'Operario de Almacén',
            'start_date' => '2025-01-15',
            'end_date' => null,
            'salary' => 1400.00,
            'working_day_type' => WorkingDayType::COMPLETA->value,
            'working_hours' => 40,
            'probation_period' => 30,
            'editor_id' => $editor->id,
            'employer_id' => $employer2->id,
            'foreigner_id' => $foreigner2->id,
        ]);

        // File 3: Chioma + Invernaderos Pérez - Arraigo
        $file3 = InmigrationFile::create([
            'campaign' => '2025-2026',
            'file_code' => 'EXP-2025-003',
            'file_title' => 'Chioma Okafor - Invernaderos Pérez',
            'application_type' => ApplicationType::EX_10->value,
            'status' => ImmigrationFileStatus::PRESENTADO->value,
            'job_title' => 'Auxiliar de Invernadero',
            'start_date' => '2025-03-01',
            'end_date' => null,
            'salary' => 1100.00,
            'working_day_type' => WorkingDayType::PARCIAL->value,
            'working_hours' => 30,
            'probation_period' => 15,
            'editor_id' => $editor->id,
            'employer_id' => $employer3->id,
            'foreigner_id' => $foreigner3->id,
        ]);

        $this->command->info('Created 3 immigration files.');
        $this->command->info('Demo data seeded successfully!');
    }

    /**
     * Create address for an entity
     */
    private function createAddress($entity, $country, $province, $municipality, array $data): void
    {
        Address::create([
            'addressable_type' => get_class($entity),
            'addressable_id' => $entity->id,
            'street_name' => $data['street_name'],
            'number' => $data['number'],
            'floor_door' => $data['floor_door'] ?? null,
            'postal_code' => $data['postal_code'],
            'municipality_id' => $municipality?->id,
            'province_id' => $province?->id,
            'country_id' => $country->id,
        ]);
    }
}
