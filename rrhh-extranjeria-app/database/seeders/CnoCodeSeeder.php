<?php

namespace Database\Seeders;

use App\Models\CnoCode;
use Illuminate\Database\Seeder;

class CnoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/cno_codes.json');

        if (! file_exists($jsonPath)) {
            $this->command->error('No se encontró el archivo cno_codes.json en database/data/');
            return;
        }

        $codes = json_decode(file_get_contents($jsonPath), true);

        if (empty($codes)) {
            $this->command->error('El archivo cno_codes.json está vacío o tiene formato inválido.');
            return;
        }

        $now = now();
        $chunks = array_chunk($codes, 500);

        foreach ($chunks as $chunk) {
            $rows = array_map(fn ($entry) => [
                'code' => $entry['code'],
                'description' => $entry['description'],
                'group_code' => $entry['group_code'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk);

            CnoCode::upsert($rows, ['code'], ['description', 'group_code', 'updated_at']);
        }

        $this->command->info('Seeded ' . count($codes) . ' CNO SEPE codes.');
    }
}
