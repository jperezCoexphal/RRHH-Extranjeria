<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\WorkingDayType;
use App\Http\Requests\InmigrationFile\StoreInmigrationFileRequest;
use App\Http\Requests\InmigrationFile\UpdateInmigrationFileRequest;
use App\Http\Requests\InmigrationFile\ChangeStatusRequest;
use App\Services\ChecklistService;
use App\Services\InmigrationFileService;
use Illuminate\Http\Request;

class InmigrationFileController extends Controller
{
    public function __construct(
        protected InmigrationFileService $service,
        protected ChecklistService $checklistService,
    ) {}

    // LISTAR EXPEDIENTES
    /*-----------------------------------------------------------------------------------*/

    public function index(Request $request)
    {
        $files = $this->service->getFiltered($request->all(), 15);

        // Datos para los filtros del formulario
        $statuses = ImmigrationFileStatus::cases();
        $applicationTypes = ApplicationType::cases();

        return view('inmigration-files.index', compact('files', 'statuses', 'applicationTypes'));
    }

    public function show(int $id)
    {
        $file = $this->service->findById($id);

        if (! $file) {
            return redirect()->route('inmigration-files.index')
                ->with('error', 'Expediente no encontrado.');
        }

        // Obtener resumen del checklist
        $checklistSummary = $this->checklistService->getChecklistSummary($id);

        return view('inmigration-files.show', compact('file', 'checklistSummary'));
    }

    // CREAR EXPEDIENTES
    /*-----------------------------------------------------------------------------------*/

    public function create()
    {
        $applicationTypes = ApplicationType::cases();
        $workingDayTypes = WorkingDayType::cases();

        return view('inmigration-files.create', compact('applicationTypes', 'workingDayTypes'));
    }

    public function store(StoreInmigrationFileRequest $request)
    {
        try {
            // Agregar el usuario autenticado como editor
            $data = $request->validated();
            $data['editor_id'] = auth()->id();

            $file = $this->service->create($data);

            return redirect()->route('inmigration-files.show', $file->id)
                ->with('success', 'Expediente creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear el expediente: ' . $e->getMessage());
        }
    }

    // ACTUALIZAR EXPEDIENTES
    /*-----------------------------------------------------------------------------------*/

    public function edit(int $id)
    {
        $file = $this->service->findById($id);

        if (! $file) {
            return redirect()->route('inmigration-files.index')
                ->with('error', 'Expediente no encontrado.');
        }

        if (! $file->status->isEditable()) {
            return redirect()->route('inmigration-files.show', $id)
                ->with('error', 'El expediente no puede ser editado en su estado actual.');
        }

        $applicationTypes = ApplicationType::cases();
        $workingDayTypes = WorkingDayType::cases();

        return view('inmigration-files.edit', compact('file', 'applicationTypes', 'workingDayTypes'));
    }

    public function update(UpdateInmigrationFileRequest $request, int $id)
    {
        try {
            $updated = $this->service->update($id, $request->validated());

            if (! $updated) {
                return back()->withInput()
                    ->with('error', 'No se pudo actualizar el expediente.');
            }

            return redirect()->route('inmigration-files.show', $id)
                ->with('success', 'Expediente actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar el expediente: ' . $e->getMessage());
        }
    }

    // CAMBIAR ESTADO
    /*-----------------------------------------------------------------------------------*/

    public function changeStatus(ChangeStatusRequest $request, int $id)
    {
        try {
            $newStatus = ImmigrationFileStatus::from($request->validated()['status']);
            $result = $this->service->changeStatus($id, $newStatus);

            if (! $result['success']) {
                return back()->with('error', $result['message']);
            }

            $message = "Estado cambiado a '{$result['new_status']}'. ";
            if (count($result['requirements_created']) > 0) {
                $message .= count($result['requirements_created']) . ' requisitos generados.';
            }

            return redirect()->route('inmigration-files.show', $id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    // ELIMINAR EXPEDIENTES
    /*-----------------------------------------------------------------------------------*/

    public function destroy(int $id)
    {
        try {
            $deleted = $this->service->delete($id);

            if (! $deleted) {
                return back()->with('error', 'No se pudo eliminar el expediente.');
            }

            return redirect()->route('inmigration-files.index')
                ->with('success', 'Expediente eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el expediente: ' . $e->getMessage());
        }
    }

    // ESTADÍSTICAS
    /*-----------------------------------------------------------------------------------*/

    public function statistics(Request $request)
    {
        $campaign = $request->get('campaign');
        $statistics = $this->service->getStatistics($campaign);

        return view('inmigration-files.statistics', compact('statistics', 'campaign'));
    }
}
