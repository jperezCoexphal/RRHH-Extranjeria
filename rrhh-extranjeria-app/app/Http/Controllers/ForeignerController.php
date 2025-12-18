<?php

namespace App\Http\Controllers;

use App\Filters\Foreigner\FilterByGender;
use App\Filters\Foreigner\FilterByName;
use App\Filters\Foreigner\FilterByNationality;
use App\Filters\Foreigner\FilterByNie;
use App\Filters\Foreigner\FilterByPassport;
use App\Http\Requests\Foreigner\StoreForeignerRequest;
use App\Http\Requests\Foreigner\UpdateForeignerRequest;
use App\Services\ForeignerService;

class ForeignerController extends Controller
{
    public function __construct(protected ForeignerService $service) {}

    // GET FOREIGNERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function index()
    {
        $filters = [
            FilterByName::class,
            FilterByNie::class,
            FilterByPassport::class,
            FilterByGender::class,
            FilterByNationality::class,
        ];

        $foreigners = $this->service->getFiltered($filters, 15);

        return view('foreigners.index', compact('foreigners'));
    }

    public function show(int $id)
    {
        $foreigner = $this->service->findById($id);

        if (!$foreigner) {
            return redirect()->route('foreigners.index')
                ->with('error', 'Extranjero no encontrado.');
        }

        return view('foreigners.show', compact('foreigner'));
    }

    // CREATE NEW FOREIGNERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function create()
    {
        return view('foreigners.create');
    }

    public function store(StoreForeignerRequest $request)
    {
        try {
            $foreigner = $this->service->create($request->validated());

            return redirect()->route('foreigners.show', $foreigner->id)
                ->with('success', 'Extranjero creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear el extranjero: ' . $e->getMessage());
        }
    }

    // UPDATE FOREIGNERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function edit(int $id)
    {
        $foreigner = $this->service->findById($id);

        if (!$foreigner) {
            return redirect()->route('foreigners.index')
                ->with('error', 'Extranjero no encontrado.');
        }

        return view('foreigners.edit', compact('foreigner'));
    }

    public function update(UpdateForeignerRequest $request, int $id)
    {
        try {
            $updated = $this->service->update($id, $request->validated());

            if (!$updated) {
                return back()->withInput()
                    ->with('error', 'No se pudo actualizar el extranjero.');
            }

            return redirect()->route('foreigners.show', $id)
                ->with('success', 'Extranjero actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar el extranjero: ' . $e->getMessage());
        }
    }

    // DELETE FOREIGNERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function destroy(int $id)
    {
        try {
            $deleted = $this->service->delete($id);

            if (!$deleted) {
                return back()->with('error', 'No se pudo eliminar el extranjero.');
            }

            return redirect()->route('foreigners.index')
                ->with('success', 'Extranjero eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el extranjero: ' . $e->getMessage());
        }
    }
}
