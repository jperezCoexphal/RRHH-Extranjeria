<?php

namespace App\Http\Controllers;

use App\Filters\Employer\FilterByAssociated;
use App\Filters\Employer\FilterByLegalForm;
use App\Filters\Employer\FilterByName;
use App\Filters\Employer\FilterByNif;
use App\Http\Requests\Employer\StoreEmployerRequest;
use App\Http\Requests\Employer\UpdateEmployerRequest;
use App\Services\EmployerService;

class EmployerController extends Controller
{
    public function __construct(protected EmployerService $service) {}

    // GET EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function index()
    {
        $filters = [
            FilterByName::class,
            FilterByNif::class,
            FilterByLegalForm::class,
            FilterByAssociated::class,
        ];

        $employers = $this->service->getFiltered($filters, 15);

        return view('employers.index', compact('employers'));
    }

    public function show(int $id)
    {
        $employer = $this->service->findById($id);

        if (!$employer) {
            return redirect()->route('employers.index')
                ->with('error', 'Empleador no encontrado.');
        }

        return view('employers.show', compact('employer'));
    }

    // CREATE NEW EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function create()
    {
        return view('employers.create');
    }

    public function store(StoreEmployerRequest $request)
    {
        try {
            $employer = $this->service->create($request->validated());
            return redirect()->route('employers.show', $employer->id)
                ->with('success', 'Empleador creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear el empleador: ' . $e->getMessage());
        }
    }

    // UPDATE EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function edit(int $id)
    {
        $employer = $this->service->findById($id);

        if (!$employer) {
            return redirect()->route('employers.index')
                ->with('error', 'Empleador no encontrado.');
        }

        return view('employers.edit', compact('employer'));
    }

    public function update(UpdateEmployerRequest $request, int $id)
    {
        try {
            $updated = $this->service->update($id, $request->validated());

            if (!$updated) {
                return back()->withInput()
                    ->with('error', 'No se pudo actualizar el empleador.');
            }

            return redirect()->route('employers.show', $id)
                ->with('success', 'Empleador actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar el empleador: ' . $e->getMessage());
        }
    }

    // DELETE EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function destroy(int $id)
    {
        try {
            $deleted = $this->service->delete($id);

            if (!$deleted) {
                return back()->with('error', 'No se pudo eliminar el empleador.');
            }

            return redirect()->route('employers.index')
                ->with('success', 'Empleador eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el empleador: ' . $e->getMessage());
        }
    }
}
