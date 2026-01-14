<?php

namespace App\Services;

use App\DTOs\InmigrationFileDTO;
use App\Enums\ImmigrationFileStatus;
use App\Filters\InmigrationFile\FilterByApplicationType;
use App\Filters\InmigrationFile\FilterByCampaign;
use App\Filters\InmigrationFile\FilterByDateRange;
use App\Filters\InmigrationFile\FilterByEmployer;
use App\Filters\InmigrationFile\FilterByFileCode;
use App\Filters\InmigrationFile\FilterByForeigner;
use App\Filters\InmigrationFile\FilterByStatus;
use App\Models\InmigrationFile;
use App\Repositories\Contracts\InmigrationFileRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para la gestión de expedientes de inmigración
 */
class InmigrationFileService
{
    /**
     * Filtros disponibles para las búsquedas
     */
    protected array $filters = [
        FilterByStatus::class,
        FilterByCampaign::class,
        FilterByApplicationType::class,
        FilterByFileCode::class,
        FilterByEmployer::class,
        FilterByForeigner::class,
        FilterByDateRange::class,
    ];

    public function __construct(
        protected InmigrationFileRepository $repository,
        protected ChecklistService $checklistService,
    ) {}

    /**
     * Obtiene todos los expedientes
     */
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Obtiene expedientes paginados
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Obtiene expedientes filtrados mediante Pipeline
     */
    public function getFiltered(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return app(Pipeline::class)
            ->send($this->repository->query())
            ->through($this->filters)
            ->thenReturn()
            ->with(['employer', 'foreigner', 'editor'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Busca un expediente por ID
     */
    public function findById(int $id): ?InmigrationFile
    {
        return $this->repository->findByIdWithRelations($id);
    }

    /**
     * Crea un nuevo expediente
     */
    public function create(array $data): InmigrationFile
    {
        return DB::transaction(function () use ($data) {
            // Establecer estado inicial
            $data['status'] = $data['status'] ?? ImmigrationFileStatus::BORRADOR->value;

            $dto = InmigrationFileDTO::fromRequest($data);
            $inmigrationFile = $this->repository->create($dto);

            // Generar requisitos iniciales si el estado tiene plantillas asociadas
            $this->checklistService->processStatusChange(
                $inmigrationFile->id,
                ImmigrationFileStatus::from($data['status'])
            );

            return $inmigrationFile->fresh([
                'employer',
                'foreigner',
                'editor',
                'requirements',
            ]);
        });
    }

    /**
     * Actualiza un expediente existente
     */
    public function update(int $id, array $data): bool
    {
        $file = $this->repository->findById($id);

        if (! $file) {
            return false;
        }

        // Verificar si el expediente es editable
        if (! $file->status->isEditable()) {
            throw new \Exception(
                "El expediente no puede ser editado en estado: {$file->status->label()}"
            );
        }

        $dto = InmigrationFileDTO::fromRequest(array_merge(
            $data,
            [
                'editor_id' => $data['editor_id'] ?? $file->editor_id,
                'employer_id' => $data['employer_id'] ?? $file->employer_id,
                'foreigner_id' => $data['foreigner_id'] ?? $file->foreigner_id,
            ]
        ));

        return $this->repository->update($id, $dto);
    }

    /**
     * Cambia el estado de un expediente
     */
    public function changeStatus(int $id, ImmigrationFileStatus $newStatus): array
    {
        return $this->checklistService->processStatusChange($id, $newStatus);
    }

    /**
     * Elimina un expediente (soft delete)
     */
    public function delete(int $id): bool
    {
        $file = $this->repository->findById($id);

        if (! $file) {
            return false;
        }

        // Solo se puede eliminar en estado borrador
        if ($file->status !== ImmigrationFileStatus::BORRADOR) {
            throw new \Exception(
                'Solo se pueden eliminar expedientes en estado Borrador'
            );
        }

        return $this->repository->delete($id);
    }

    /**
     * Obtiene expedientes por campaña
     */
    public function getByCampaign(string $campaign): Collection
    {
        return $this->repository->findByCampaign($campaign);
    }

    /**
     * Obtiene expedientes por estado
     */
    public function getByStatus(ImmigrationFileStatus $status): Collection
    {
        return $this->repository->findByStatus($status);
    }

    /**
     * Obtiene expedientes con requisitos vencidos
     */
    public function getWithOverdueRequirements(): Collection
    {
        return $this->repository->getWithOverdueRequirements();
    }

    /**
     * Obtiene estadísticas de expedientes
     */
    public function getStatistics(?string $campaign = null): array
    {
        $query = $this->repository->query();

        if ($campaign) {
            $query->where('campaign', $campaign);
        }

        $total = $query->count();

        $byStatus = [];
        foreach (ImmigrationFileStatus::cases() as $status) {
            $count = (clone $query)->where('status', $status)->count();
            if ($count > 0) {
                $byStatus[$status->value] = [
                    'label' => $status->label(),
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                ];
            }
        }

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'with_overdue_requirements' => $this->repository->getWithOverdueRequirements()->count(),
        ];
    }

    /**
     * Genera el código de expediente automáticamente
     */
    public function generateFileCode(string $campaign, string $applicationType): string
    {
        // Formato: CAMPAIGN-TYPE-SEQUENCE (ej: 2025-EX03-0001)
        $year = substr($campaign, 0, 4);
        $typeCode = str_replace('-', '', $applicationType);

        $lastFile = $this->repository->query()
            ->where('campaign', $campaign)
            ->where('application_type', $applicationType)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastFile) {
            // Extraer secuencia del último código
            $parts = explode('-', $lastFile->file_code);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        }

        return sprintf('%s-%s-%04d', $year, $typeCode, $sequence);
    }
}
