<?php

namespace App\Services;

use App\DTOs\FileRequirementDTO;
use App\Enums\ImmigrationFileStatus;
use App\Enums\TargetEntity;
use App\Models\InmigrationFile;
use App\Repositories\Contracts\FileRequirementRepository;
use App\Repositories\Contracts\InmigrationFileRepository;
use App\Repositories\Contracts\RequirementTemplateRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar el checklist de requisitos de un expediente
 * Genera automáticamente los requisitos según las plantillas al cambiar el estado
 */
class ChecklistService
{
    public function __construct(
        protected InmigrationFileRepository $inmigrationFileRepository,
        protected RequirementTemplateRepository $templateRepository,
        protected FileRequirementRepository $requirementRepository,
    ) {}

    /**
     * Procesa el cambio de estado de un expediente
     * Genera los requisitos correspondientes según las plantillas
     *
     * @param  int  $inmigrationFileId  ID del expediente
     * @param  ImmigrationFileStatus  $newStatus  Nuevo estado del expediente
     * @return array Resultado del proceso con los requisitos generados
     */
    public function processStatusChange(
        int $inmigrationFileId,
        ImmigrationFileStatus $newStatus
    ): array {
        $inmigrationFile = $this->inmigrationFileRepository->findByIdWithRelations($inmigrationFileId);

        if (! $inmigrationFile) {
            return [
                'success' => false,
                'message' => 'Expediente no encontrado',
                'requirements_created' => [],
            ];
        }

        $oldStatus = $inmigrationFile->status;

        // Validar que la transición es permitida
        if (! $oldStatus->canTransitionTo($newStatus)) {
            return [
                'success' => false,
                'message' => "No se permite la transición de {$oldStatus->label()} a {$newStatus->label()}",
                'requirements_created' => [],
            ];
        }

        return DB::transaction(function () use ($inmigrationFile, $newStatus) {
            // Actualizar el estado
            $this->inmigrationFileRepository->updateStatus(
                $inmigrationFile->id,
                $newStatus
            );

            // Generar los requisitos según las plantillas
            $createdRequirements = $this->generateRequirementsFromTemplates(
                $inmigrationFile,
                $newStatus
            );

            Log::info('ChecklistService: Requisitos generados para expediente', [
                'file_id' => $inmigrationFile->id,
                'file_code' => $inmigrationFile->file_code,
                'new_status' => $newStatus->value,
                'requirements_count' => $createdRequirements->count(),
            ]);

            return [
                'success' => true,
                'message' => 'Estado actualizado y requisitos generados correctamente',
                'new_status' => $newStatus->label(),
                'requirements_created' => $createdRequirements->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'target_entity' => $r->target_entity?->label(),
                    'due_date' => $r->due_date?->format('d/m/Y'),
                    'is_mandatory' => $r->is_mandatory,
                ])->toArray(),
            ];
        });
    }

    /**
     * Genera los requisitos desde las plantillas para un expediente y estado
     */
    protected function generateRequirementsFromTemplates(
        InmigrationFile $inmigrationFile,
        ImmigrationFileStatus $status
    ): Collection {
        // Obtener plantillas aplicables
        $templates = $this->templateRepository->getTemplatesForFile(
            $inmigrationFile->application_type,
            $status
        );

        if ($templates->isEmpty()) {
            return collect();
        }

        // Obtener IDs de plantillas ya aplicadas al expediente
        $existingTemplateIds = $inmigrationFile->requirements()
            ->whereNotNull('requirement_template_id')
            ->pluck('requirement_template_id')
            ->toArray();

        // Filtrar plantillas que ya fueron aplicadas
        $newTemplates = $templates->filter(
            fn ($template) => ! in_array($template->id, $existingTemplateIds)
        );

        if ($newTemplates->isEmpty()) {
            return collect();
        }

        // Crear los DTOs para los nuevos requisitos
        $baseDate = now();
        $dtos = $newTemplates->map(
            fn ($template) => FileRequirementDTO::fromTemplate(
                $template,
                $inmigrationFile->id,
                $baseDate
            )
        )->toArray();

        // Crear los requisitos en batch
        return $this->requirementRepository->createMany($dtos);
    }

    /**
     * Regenera los requisitos para un expediente (forzar recreación)
     * Útil cuando se modifican las plantillas
     */
    public function regenerateRequirements(int $inmigrationFileId): array
    {
        $inmigrationFile = $this->inmigrationFileRepository->findByIdWithRelations($inmigrationFileId);

        if (! $inmigrationFile) {
            return [
                'success' => false,
                'message' => 'Expediente no encontrado',
            ];
        }

        // Obtener plantillas para el estado actual
        $templates = $this->templateRepository->getTemplatesForFile(
            $inmigrationFile->application_type,
            $inmigrationFile->status
        );

        // Obtener requisitos actuales que vienen de plantillas
        $existingFromTemplates = $inmigrationFile->requirements()
            ->whereNotNull('requirement_template_id')
            ->get()
            ->keyBy('requirement_template_id');

        $created = [];
        $updated = [];
        $baseDate = now();

        foreach ($templates as $template) {
            if ($existingFromTemplates->has($template->id)) {
                // Actualizar requisito existente (solo nombre y descripción)
                $existing = $existingFromTemplates->get($template->id);
                if (! $existing->is_completed) {
                    $this->requirementRepository->update($existing->id, [
                        'name' => $template->name,
                        'description' => $template->description,
                    ]);
                    $updated[] = $existing->id;
                }
            } else {
                // Crear nuevo requisito
                $dto = FileRequirementDTO::fromTemplate($template, $inmigrationFile->id, $baseDate);
                $requirement = $this->requirementRepository->create($dto);
                $created[] = $requirement->id;
            }
        }

        return [
            'success' => true,
            'message' => 'Requisitos regenerados correctamente',
            'created_count' => count($created),
            'updated_count' => count($updated),
        ];
    }

    /**
     * Obtiene el resumen del checklist de un expediente
     */
    public function getChecklistSummary(int $inmigrationFileId): array
    {
        $requirements = $this->requirementRepository->getByInmigrationFile($inmigrationFileId);

        $summary = [
            'total' => $requirements->count(),
            'completed' => $requirements->where('is_completed', true)->count(),
            'pending' => $requirements->where('is_completed', false)->count(),
            'mandatory_pending' => $requirements
                ->where('is_completed', false)
                ->where('is_mandatory', true)
                ->count(),
            'overdue' => $requirements
                ->filter(fn ($r) => ! $r->is_completed && $r->due_date?->isPast())
                ->count(),
            'by_entity' => [],
        ];

        // Agrupar por entidad
        foreach (TargetEntity::cases() as $entity) {
            $entityRequirements = $requirements->where('target_entity', $entity);
            if ($entityRequirements->isNotEmpty()) {
                $summary['by_entity'][$entity->value] = [
                    'label' => $entity->label(),
                    'total' => $entityRequirements->count(),
                    'completed' => $entityRequirements->where('is_completed', true)->count(),
                    'pending' => $entityRequirements->where('is_completed', false)->count(),
                ];
            }
        }

        // Calcular porcentaje de completitud
        $summary['completion_percentage'] = $summary['total'] > 0
            ? round(($summary['completed'] / $summary['total']) * 100, 1)
            : 0;

        // Determinar si está listo para generar documentos
        $summary['ready_for_documents'] = $summary['mandatory_pending'] === 0;

        return $summary;
    }

    /**
     * Marca un requisito como completado
     */
    public function completeRequirement(int $requirementId): array
    {
        $requirement = $this->requirementRepository->findById($requirementId);

        if (! $requirement) {
            return [
                'success' => false,
                'message' => 'Requisito no encontrado',
            ];
        }

        if ($requirement->is_completed) {
            return [
                'success' => false,
                'message' => 'El requisito ya está completado',
            ];
        }

        $this->requirementRepository->markAsCompleted($requirementId);

        // Obtener resumen actualizado
        $summary = $this->getChecklistSummary($requirement->inmigration_file_id);

        return [
            'success' => true,
            'message' => 'Requisito marcado como completado',
            'requirement' => [
                'id' => $requirement->id,
                'name' => $requirement->name,
                'completed_at' => now()->format('d/m/Y H:i'),
            ],
            'checklist_summary' => $summary,
        ];
    }

    /**
     * Agrega un requisito manual (no basado en plantilla)
     */
    public function addManualRequirement(
        int $inmigrationFileId,
        string $name,
        ?string $description = null,
        ?TargetEntity $targetEntity = null,
        ?Carbon $dueDate = null,
        bool $isMandatory = false
    ): array {
        $inmigrationFile = $this->inmigrationFileRepository->findById($inmigrationFileId);

        if (! $inmigrationFile) {
            return [
                'success' => false,
                'message' => 'Expediente no encontrado',
            ];
        }

        $dto = new FileRequirementDTO(
            name: $name,
            inmigration_file_id: $inmigrationFileId,
            is_mandatory: $isMandatory,
            description: $description,
            target_entity: $targetEntity,
            due_date: $dueDate,
        );

        $requirement = $this->requirementRepository->create($dto);

        return [
            'success' => true,
            'message' => 'Requisito agregado correctamente',
            'requirement' => [
                'id' => $requirement->id,
                'name' => $requirement->name,
                'target_entity' => $requirement->target_entity?->label(),
                'due_date' => $requirement->due_date?->format('d/m/Y'),
                'is_mandatory' => $requirement->is_mandatory,
            ],
        ];
    }

    /**
     * Obtiene los requisitos próximos a vencer (próximos N días)
     */
    public function getUpcomingDueRequirements(int $inmigrationFileId, int $days = 7): array
    {
        $requirements = $this->requirementRepository->getPendingByInmigrationFile($inmigrationFileId);

        $upcoming = $requirements->filter(function ($requirement) use ($days) {
            if (! $requirement->due_date) {
                return false;
            }

            $daysUntilDue = now()->diffInDays($requirement->due_date, false);

            return $daysUntilDue >= 0 && $daysUntilDue <= $days;
        });

        return $upcoming->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'target_entity' => $r->target_entity?->label(),
            'due_date' => $r->due_date->format('d/m/Y'),
            'days_remaining' => now()->diffInDays($r->due_date, false),
            'is_mandatory' => $r->is_mandatory,
        ])->toArray();
    }

    /**
     * Elimina un requisito (solo si no está completado y no es de plantilla)
     */
    public function deleteRequirement(int $requirementId): array
    {
        $requirement = $this->requirementRepository->findById($requirementId);

        if (! $requirement) {
            return [
                'success' => false,
                'message' => 'Requisito no encontrado',
            ];
        }

        if ($requirement->is_completed) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar un requisito completado',
            ];
        }

        if ($requirement->requirement_template_id !== null) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar un requisito generado desde plantilla',
            ];
        }

        $this->requirementRepository->delete($requirementId);

        return [
            'success' => true,
            'message' => 'Requisito eliminado correctamente',
        ];
    }
}
