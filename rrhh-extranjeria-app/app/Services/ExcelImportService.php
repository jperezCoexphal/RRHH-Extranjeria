<?php

namespace App\Services;

use App\Enums\ImmigrationFileStatus;
use App\Enums\LegalForm;
use App\Enums\ApplicationType;
use App\Models\Employer;
use App\Models\Foreigner;
use App\Models\InmigrationFile;
use App\Models\Municipality;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ExcelImportService
{
    protected array $stats = [
        'employers_created' => 0,
        'employers_updated' => 0,
        'foreigners_created' => 0,
        'foreigners_updated' => 0,
        'files_created' => 0,
        'files_updated' => 0,
        'errors' => [],
    ];

    // Default column map (sheets 0-5: 2017-2022)
    protected array $defaultColumnMap = [
        'B' => 'asociado',
        'C' => 'presentacion',
        'D' => 'cif',
        'E' => 'nombre_cliente',
        'F' => 'domicilio',
        'G' => 'codigo_postal',
        'H' => 'localidad',
        'I' => 'telefono',
        'J' => 'email',
        'U' => 'nie',
        'V' => 'exp',
        'W' => 'fecha_nacimiento',
        'Y' => 'estado_expediente',
    ];

    // Column map for sheets 6-8 (2023-2025) where column D is a marker
    protected array $recentColumnMap = [
        'B' => 'asociado',
        'C' => 'presentacion',
        'E' => 'cif',           // CIF moved to column E
        'F' => 'nombre_cliente', // Name moved to column F
        'G' => 'domicilio',
        'H' => 'codigo_postal',
        'I' => 'localidad',
        'J' => 'telefono',
        'K' => 'email',
        'V' => 'nie',           // NIE column varies
        'W' => 'exp',
        'X' => 'fecha_nacimiento',
        'Z' => 'estado_expediente',
    ];

    protected array $columnMap;

    public function import(string $filePath, ?int $sheetIndex = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheetsToProcess = $sheetIndex !== null
            ? [$sheetIndex]
            : range(0, $spreadsheet->getSheetCount() - 1);

        foreach ($sheetsToProcess as $index) {
            $this->processSheet($spreadsheet->getSheet($index), $index);
        }

        return $this->stats;
    }

    protected function processSheet($sheet, int $sheetIndex): void
    {
        $sheetName = $sheet->getTitle();
        $highestRow = $sheet->getHighestRow();

        // Select column map based on sheet index (years 2023+ have different structure)
        $this->columnMap = $sheetIndex >= 6 ? $this->recentColumnMap : $this->defaultColumnMap;

        Log::info("Processing sheet: {$sheetName} with {$highestRow} rows, using " . ($sheetIndex >= 6 ? 'recent' : 'default') . " column map");

        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $rowData = $this->extractRowData($sheet, $row);

                if (empty($rowData['cif']) && empty($rowData['nie'])) {
                    continue; // Skip empty rows
                }

                DB::transaction(function () use ($rowData, $sheetName) {
                    $employer = null;
                    $foreigner = null;

                    // Create/update employer if CIF exists
                    if (!empty($rowData['cif'])) {
                        $employer = $this->createOrUpdateEmployer($rowData);
                    }

                    // Create/update foreigner if NIE exists
                    if (!empty($rowData['nie'])) {
                        $foreigner = $this->createOrUpdateForeigner($rowData);
                    }

                    // Create inmigration file if we have EXP number
                    if (!empty($rowData['exp']) && ($employer || $foreigner)) {
                        $this->createOrUpdateInmigrationFile($rowData, $employer, $foreigner, $sheetName);
                    }
                });
            } catch (\Exception $e) {
                $this->stats['errors'][] = "Row {$row} in {$sheetName}: " . $e->getMessage();
                Log::error("Import error at row {$row}: " . $e->getMessage());
            }
        }
    }

    protected function extractRowData($sheet, int $row): array
    {
        $data = [];
        foreach ($this->columnMap as $col => $field) {
            $value = $sheet->getCell($col . $row)->getValue();

            // Handle dates (Excel serial numbers)
            if (in_array($field, ['presentacion', 'fecha_nacimiento']) && is_numeric($value)) {
                try {
                    $value = ExcelDate::excelToDateTimeObject($value);
                } catch (\Exception $e) {
                    $value = null;
                }
            }

            $data[$field] = $value;
        }

        return $data;
    }

    protected function createOrUpdateEmployer(array $data): ?Employer
    {
        $cif = $this->cleanIdentifier($data['cif']);
        if (empty($cif) || !$this->isValidCif($cif)) {
            return null;
        }

        $employer = Employer::where('nif', $cif)->first();
        $isNew = !$employer;

        $email = $this->cleanEmail($data['email'] ?? '');
        // Check if email already exists (many employers share the agency's email)
        if ($email && Employer::where('email', $email)->exists()) {
            $email = null;
        }

        $employerData = [
            'nif' => $cif,
            'comercial_name' => $data['nombre_cliente'] ?? 'Sin nombre',
            'fiscal_name' => $data['nombre_cliente'] ?? null,
            'legal_form' => $this->determineLegalForm($cif),
            'is_associated' => $this->isAssociated($data['asociado'] ?? ''),
            'phone' => $this->cleanPhone($data['telefono'] ?? ''),
            'email' => $email,
        ];

        if ($isNew) {
            $employer = Employer::create($employerData);
            $this->stats['employers_created']++;

            // Create address if we have location data
            if (!empty($data['domicilio']) || !empty($data['localidad'])) {
                $this->createEmployerAddress($employer, $data);
            }
        } else {
            // Only update if new data is more complete
            $employer->update(array_filter($employerData, fn($v) => $v !== null && $v !== ''));
            $this->stats['employers_updated']++;
        }

        return $employer;
    }

    protected function createEmployerAddress(Employer $employer, array $data): void
    {
        $municipality = null;
        if (!empty($data['localidad'])) {
            $municipality = Municipality::where('municipality_name', 'like', '%' . $data['localidad'] . '%')->first();
        }

        $employer->address()->create([
            'street_name' => $data['domicilio'] ?? 'Sin direccion',
            'postal_code' => $data['codigo_postal'] ?? '00000',
            'country_id' => 1, // Spain
            'province_id' => $municipality?->province_id ?? 4, // Default Almeria
            'municipality_id' => $municipality?->id,
        ]);
    }

    protected function createOrUpdateForeigner(array $data): ?Foreigner
    {
        $nie = $this->cleanIdentifier($data['nie']);
        if (empty($nie) || !$this->isValidNie($nie)) {
            return null;
        }

        $foreigner = Foreigner::where('nie', $nie)->first();
        $isNew = !$foreigner;

        // Try to extract name from cliente if foreigner doesn't exist
        $name = $this->extractForeignerName($data['nombre_cliente'] ?? '');

        $foreignerData = [
            'nie' => $nie,
            'first_name' => $name['first_name'] ?? 'Sin nombre',
            'last_name' => $name['last_name'] ?? '',
            'birthdate' => $this->parseDate($data['fecha_nacimiento'] ?? null),
        ];

        if ($isNew) {
            $foreigner = Foreigner::create($foreignerData);
            $this->stats['foreigners_created']++;
        } else {
            // Only update birthdate if we have it and foreigner doesn't
            if (!empty($foreignerData['birthdate']) && empty($foreigner->birthdate)) {
                $foreigner->update(['birthdate' => $foreignerData['birthdate']]);
            }
            $this->stats['foreigners_updated']++;
        }

        return $foreigner;
    }

    protected function createOrUpdateInmigrationFile(array $data, ?Employer $employer, ?Foreigner $foreigner, string $sheetName): void
    {
        $fileCode = $this->cleanIdentifier($data['exp']);
        if (empty($fileCode)) {
            return;
        }

        $file = InmigrationFile::where('file_code', $fileCode)->first();
        $isNew = !$file;

        // Extract year from sheet name (e.g., "EXPT. EXTRANJERIA 21" -> 2021)
        preg_match('/(\d{2})$/', $sheetName, $matches);
        $year = isset($matches[1]) ? '20' . $matches[1] : date('Y');

        $startDate = $this->parseDate($data['presentacion'] ?? null);

        $fileData = [
            'file_code' => $fileCode,
            'file_title' => "Expediente {$fileCode}",
            'campaign' => $year,
            'employer_id' => $employer?->id,
            'foreigner_id' => $foreigner?->id,
            'application_type' => ApplicationType::EX_03, // Default: trabajo por cuenta ajena
            'status' => $this->parseImmigrationFileStatus($data['estado_expediente'] ?? ''),
            'start_date' => $startDate,
        ];

        if ($isNew) {
            InmigrationFile::create($fileData);
            $this->stats['files_created']++;
        } else {
            // Update only if we have more data
            $file->update(array_filter($fileData, fn($v) => $v !== null));
            $this->stats['files_updated']++;
        }
    }

    protected function determineLegalForm(string $cif): LegalForm
    {
        $firstChar = strtoupper(substr($cif, 0, 1));

        return match ($firstChar) {
            'A' => LegalForm::SA,    // Sociedad Anonima
            'B' => LegalForm::SL,    // Sociedad Limitada
            'C' => LegalForm::SC,    // Sociedad Colectiva
            'D' => LegalForm::SCS,   // Sociedad Comanditaria Simple
            'E' => LegalForm::CB,    // Comunidad de Bienes
            'F' => LegalForm::COOP,  // Cooperativa
            'G' => LegalForm::AIE,   // Agrupacion de Interes Economico
            'J' => LegalForm::SCP,   // Sociedad Civil Privada
            'V' => LegalForm::SAT,   // Sociedad Agraria de Transformacion
            default => LegalForm::EI, // Empresario Individual (Autonomo)
        };
    }

    protected function isAssociated(string $asociado): bool
    {
        $asociado = strtolower(trim($asociado));
        return !empty($asociado) && $asociado !== 'externo';
    }

    protected function parseImmigrationFileStatus(?string $status): ImmigrationFileStatus
    {
        if (empty($status)) {
            return ImmigrationFileStatus::BORRADOR;
        }

        $status = strtolower(trim($status));

        return match (true) {
            str_contains($status, 'favorable') => ImmigrationFileStatus::FAVORABLE,
            str_contains($status, 'denegad') => ImmigrationFileStatus::DENEGADO,
            str_contains($status, 'present') => ImmigrationFileStatus::PRESENTADO,
            str_contains($status, 'requerid') => ImmigrationFileStatus::REQUERIDO,
            str_contains($status, 'archiv') => ImmigrationFileStatus::ARCHIVADO,
            str_contains($status, 'pendiente') => ImmigrationFileStatus::PENDIENTE_REVISION,
            str_contains($status, 'listo') => ImmigrationFileStatus::LISTO,
            default => ImmigrationFileStatus::BORRADOR,
        };
    }

    protected function parseDate($value): ?Carbon
    {
        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                return null;
            }
        }

        if (is_string($value) && !empty($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    protected function extractForeignerName(?string $fullName): array
    {
        if (empty($fullName)) {
            return ['first_name' => 'Sin nombre', 'last_name' => ''];
        }

        $parts = explode(' ', trim($fullName), 2);

        return [
            'first_name' => $parts[0] ?? 'Sin nombre',
            'last_name' => $parts[1] ?? '',
        ];
    }

    protected function cleanIdentifier(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Remove spaces, normalize
        $cleaned = strtoupper(preg_replace('/\s+/', '', trim($value)));

        // Skip invalid values
        $invalidValues = ['NUEVO', 'CLIENTES', 'NUEVOS', 'HACERFACTURA', 'COLUMNA1', 'COLUMNA2'];
        if (in_array($cleaned, $invalidValues)) {
            return null;
        }

        return $cleaned;
    }

    protected function isValidCif(?string $cif): bool
    {
        if (empty($cif) || strlen($cif) < 8 || strlen($cif) > 12) {
            return false;
        }

        // Skip formulas and invalid patterns
        if (str_starts_with($cif, '=') || str_starts_with($cif, 'ES')) {
            return false;
        }

        // Spanish CIF: letter + 8 alphanumeric (e.g., B04875209)
        // Spanish NIF/DNI: 8 digits + letter (e.g., 27268407K)
        // Spanish NIE: X/Y/Z + 7 digits + letter (e.g., Y1573181H)
        return (bool) preg_match('/^([ABCDEFGHJNPQRSUVW][0-9]{7,8}[A-Z0-9]?|[0-9]{7,8}[A-Z]|[XYZ][0-9]{7}[A-Z])$/i', $cif);
    }

    protected function isValidNie(?string $nie): bool
    {
        if (empty($nie) || strlen($nie) < 9 || strlen($nie) > 12) {
            return false;
        }

        // Skip formulas and invalid patterns
        if (str_starts_with($nie, '=') || str_starts_with($nie, 'ES')) {
            return false;
        }

        // Spanish NIE pattern: X/Y/Z + 7 digits + letter
        return (bool) preg_match('/^[XYZ][0-9]{7}[A-Z]$/i', $nie);
    }

    protected function cleanPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove non-numeric except + at start
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        return strlen($phone) >= 9 ? $phone : null;
    }

    protected function cleanEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
