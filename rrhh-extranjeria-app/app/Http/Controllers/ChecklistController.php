<?php

namespace App\Http\Controllers;

use App\Enums\TargetEntity;
use App\Http\Requests\Checklist\AddRequirementRequest;
use App\Http\Requests\Checklist\UpdateRequirementRequest;
use App\Services\ChecklistService;
use App\Services\InmigrationFileService;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function __construct(
        protected ChecklistService $checklistService,
        protected InmigrationFileService $fileService,
    ) {}

    // LISTAR REQUISITOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Muestra el checklist completo de un expediente
     */
    public function index(int $inmigrationFileId)
    {
        $file = $this->fileService->findById($inmigrationFileId);

        if (! $file) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Expediente no encontrado'], 404);
            }
            return redirect()->route('inmigration-files.index')
                ->with('error', 'Expediente no encontrado.');
        }

        $summary = $this->checklistService->getChecklistSummary($inmigrationFileId);
        $requirements = $file->requirements()->orderBy('target_entity')->orderBy('name')->get();
        $targetEntities = TargetEntity::cases();

        if (request()->expectsJson()) {
            return response()->json([
                'file' => $file,
                'summary' => $summary,
                'requirements' => $requirements,
            ]);
        }

        return view('checklist.index', compact('file', 'summary', 'requirements', 'targetEntities'));
    }

    /**
     * Obtiene el resumen del checklist
     */
    public function summary(int $inmigrationFileId)
    {
        $summary = $this->checklistService->getChecklistSummary($inmigrationFileId);

        if (request()->expectsJson()) {
            return response()->json($summary);
        }

        return view('checklist.summary', compact('summary'));
    }

    /**
     * Obtiene requisitos próximos a vencer
     */
    public function upcoming(int $inmigrationFileId, Request $request)
    {
        $days = $request->get('days', 7);
        $upcoming = $this->checklistService->getUpcomingDueRequirements($inmigrationFileId, $days);

        if (request()->expectsJson()) {
            return response()->json([
                'upcoming_requirements' => $upcoming,
                'days_ahead' => $days,
            ]);
        }

        return view('checklist.upcoming', compact('upcoming', 'days'));
    }

    // GESTIONAR REQUISITOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Marca un requisito como completado
     */
    public function complete(int $requirementId)
    {
        $result = $this->checklistService->completeRequirement($requirementId);

        if (request()->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Agrega un requisito manual
     */
    public function store(AddRequirementRequest $request, int $inmigrationFileId)
    {
        $data = $request->validated();

        $targetEntity = isset($data['target_entity'])
            ? TargetEntity::from($data['target_entity'])
            : null;

        $dueDate = isset($data['due_date'])
            ? \Carbon\Carbon::parse($data['due_date'])
            : null;

        $result = $this->checklistService->addManualRequirement(
            $inmigrationFileId,
            $data['name'],
            $data['description'] ?? null,
            $targetEntity,
            $dueDate,
            $data['is_mandatory'] ?? false
        );

        if (request()->expectsJson()) {
            return response()->json($result, $result['success'] ? 201 : 400);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Actualiza un requisito
     */
    public function update(UpdateRequirementRequest $request, int $requirementId)
    {
        $data = $request->validated();

        $requirement = app(\App\Repositories\Contracts\FileRequirementRepository::class)
            ->findById($requirementId);

        if (! $requirement) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Requisito no encontrado'], 404);
            }
            return back()->with('error', 'Requisito no encontrado.');
        }

        // Solo permitir actualizar si no está completado
        if ($requirement->is_completed) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'No se puede modificar un requisito completado'], 400);
            }
            return back()->with('error', 'No se puede modificar un requisito completado.');
        }

        $updated = app(\App\Repositories\Contracts\FileRequirementRepository::class)
            ->update($requirementId, $data);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $updated,
                'message' => $updated ? 'Requisito actualizado.' : 'No se pudo actualizar.',
            ]);
        }

        if ($updated) {
            return back()->with('success', 'Requisito actualizado exitosamente.');
        }

        return back()->with('error', 'No se pudo actualizar el requisito.');
    }

    /**
     * Elimina un requisito manual
     */
    public function destroy(int $requirementId)
    {
        $result = $this->checklistService->deleteRequirement($requirementId);

        if (request()->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    // REGENERAR REQUISITOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Regenera los requisitos desde las plantillas
     */
    public function regenerate(int $inmigrationFileId)
    {
        $result = $this->checklistService->regenerateRequirements($inmigrationFileId);

        if (request()->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            $message = "Requisitos regenerados. Creados: {$result['created_count']}, Actualizados: {$result['updated_count']}.";
            return back()->with('success', $message);
        }

        return back()->with('error', $result['message']);
    }

    // FILTRAR POR ENTIDAD
    /*-----------------------------------------------------------------------------------*/

    /**
     * Obtiene requisitos por entidad objetivo
     */
    public function byEntity(int $inmigrationFileId, string $entity)
    {
        try {
            $targetEntity = TargetEntity::from($entity);
        } catch (\ValueError $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Entidad no válida'], 400);
            }
            return back()->with('error', 'Entidad no válida.');
        }

        $requirements = app(\App\Repositories\Contracts\FileRequirementRepository::class)
            ->getByEntity($inmigrationFileId, $targetEntity);

        if (request()->expectsJson()) {
            return response()->json([
                'entity' => $targetEntity->label(),
                'requirements' => $requirements,
            ]);
        }

        return view('checklist.by-entity', compact('requirements', 'targetEntity'));
    }
}
