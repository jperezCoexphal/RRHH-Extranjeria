<?php

namespace App\Console\Commands;

use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportExcelCommand extends Command
{
    protected $signature = 'excel:import
        {file? : Path to Excel file (default: doc/EXPEDIENTES...xlsx)}
        {--sheet= : Specific sheet index to import (0-based)}
        {--dry-run : Simulate import without saving}';

    protected $description = 'Import data from Excel file into the database';

    public function handle(ExcelImportService $service): int
    {
        $file = $this->argument('file')
            ?? base_path('../doc/EXPEDIENTES EXTRANJERÍA Base de Datos_Copia IVÁN.xlsx');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $sheetIndex = $this->option('sheet') !== null
            ? (int) $this->option('sheet')
            : null;

        $this->info("Importing from: {$file}");

        if ($sheetIndex !== null) {
            $this->info("Processing only sheet index: {$sheetIndex}");
        } else {
            $this->info("Processing all sheets");
        }

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN - No data will be saved");
        }

        $this->newLine();

        $this->withProgressBar(1, function () use ($service, $file, $sheetIndex) {
            $service->import($file, $sheetIndex);
        });

        $this->newLine(2);

        $stats = $service->getStats();

        $this->info("Import completed!");
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Employers Created', $stats['employers_created']],
                ['Employers Updated', $stats['employers_updated']],
                ['Foreigners Created', $stats['foreigners_created']],
                ['Foreigners Updated', $stats['foreigners_updated']],
                ['Files Created', $stats['files_created']],
                ['Files Updated', $stats['files_updated']],
            ]
        );

        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->error("Errors encountered: " . count($stats['errors']));
            foreach (array_slice($stats['errors'], 0, 10) as $error) {
                $this->line("  - {$error}");
            }
            if (count($stats['errors']) > 10) {
                $this->line("  ... and " . (count($stats['errors']) - 10) . " more errors");
            }
        }

        return 0;
    }
}
