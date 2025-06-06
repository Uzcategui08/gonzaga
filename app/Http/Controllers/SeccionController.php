<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use App\Models\Grado;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $secciones = Seccion::with('grado')->orderBy('created_at', 'asc')->get();
            if (is_null($secciones)) {
                $secciones = collect([]);
            }
            return view('secciones.index', compact('secciones'));
        } catch (\Exception $e) {
            return view('secciones.index')->with('error', 'Error al cargar las secciones: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $grados = Grado::all();
        return view('secciones.create', compact('grados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'grado_id' => 'required|exists:grados,id'
        ]);

        try {
            Seccion::create($request->all());
            return redirect()->route('secciones.index')
                ->with('success', 'Sección creada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear la sección: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Seccion $seccion)
    {
        return view('secciones.show', compact('seccion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seccion $seccion)
    {
        $grados = Grado::all();
        return view('secciones.edit', compact('seccion', 'grados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seccion $seccion)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'grado_id' => 'required|exists:grados,id'
        ]);

        try {
            $seccion->update($request->all());
            return redirect()->route('secciones.index')
                ->with('success', 'Sección actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar la sección: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seccion $seccion)
    {
        try {
            $seccion->delete();
            return redirect()->route('secciones.index')
                ->with('success', 'Sección eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la sección: ' . $e->getMessage());
        }
    }    
}
