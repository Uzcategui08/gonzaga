<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $materias = Materia::orderBy('created_at', 'asc')->get();
            return view('materias.index', compact('materias'));
        } catch (\Exception $e) {
            \Log::error('Error en MateriaController@index: ' . $e->getMessage());
            return view('materias.index')->with('error', 'Error al cargar las materias: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('materias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:materias|max:255',
            'nivel' => 'required|in:primaria,secundaria'
        ]);

        Materia::create($request->all());

        return redirect()->route('materias.index')
            ->with('success', 'Materia creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Materia $materia)
    {
        return view('materias.show', compact('materia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Materia $materia)
    {
        return view('materias.edit', compact('materia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Materia $materia)
    {
        $request->validate([
            'nombre' => 'required|unique:materias,nombre,' . $materia->id . '|max:255',
            'nivel' => 'required|in:primaria,secundaria'
        ]);

        $materia->update($request->all());

        return redirect()->route('materias.index')
            ->with('success', 'Materia actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Materia $materia)
    {
        $materia->delete();

        return redirect()->route('materias.index')
            ->with('success', 'Materia eliminada exitosamente');
    }
}
