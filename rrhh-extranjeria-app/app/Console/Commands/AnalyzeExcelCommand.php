<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyzeExcelCommand extends Command
{
    protected $signature = 'excel:analyze {file? : Path to Excel file}';
    protected $description = 'Analyze the structure of an Excel file';

    public function handle(): int
    {
        $file = $this->argument('file')
            ?? base_path('../doc/EXPEDIENTES EXTRANJERÍA Base de Datos_Copia IVÁN.xlsx');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Analyzing: {$file}");
        $this->newLine();

        try {
            $spreadsheet = IOFactory::load($file);
            $sheetCount = $spreadsheet->getSheetCount();

            $this->info("Total sheets: {$sheetCount}");
            $this->newLine();

            foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
                $sheet = $spreadsheet->getSheet($index);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $this->warn("Sheet {$index}: {$sheetName}");
                $this->line("  Rows: {$highestRow}, Columns: {$highestColumn}");

                // Get headers (first row)
                $headers = [];
                $colIndex = 1;
                $colRange = $this->getColumnRange($highestColumn);
                foreach ($colRange as $col) {
                    $value = $sheet->getCell($col . '1')->getValue();
                    if ($value) {
                        $headers[$col] = $value;
                    }
                }

                if (!empty($headers)) {
                    $this->line("  Headers:");
                    foreach ($headers as $col => $header) {
                        $this->line("    [{$col}] {$header}");
                    }
                }

                // Show sample data (rows 2-4)
                if ($highestRow > 1) {
                    $this->line("  Sample data (first 3 rows):");
                    for ($row = 2; $row <= min(4, $highestRow); $row++) {
                        $rowData = [];
                        foreach (array_keys($headers) as $col) {
                            $value = $sheet->getCell($col . $row)->getValue();
                            if ($value !== null && $value !== '') {
                                $headerName = $headers[$col] ?? $col;
                                $rowData[] = substr($headerName, 0, 15) . ": " . substr((string)$value, 0, 20);
                            }
                        }
                        if (!empty($rowData)) {
                            $this->line("    Row {$row}: " . implode(' | ', array_slice($rowData, 0, 5)));
                        }
                    }
                }

                $this->newLine();
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error reading Excel: " . $e->getMessage());
            return 1;
        }
    }

    private function getColumnRange(string $highestColumn): array
    {
        $columns = [];
        $current = 'A';
        while (true) {
            $columns[] = $current;
            if ($current === $highestColumn) {
                break;
            }
            $current++;
        }
        return $columns;
    }
}
