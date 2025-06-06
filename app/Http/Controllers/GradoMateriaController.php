<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Materia;
use Illuminate\Http\Request;

class GradoMateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $grados = Grado::with('materias')->orderBy('nombre')->get();
            return view('grados.materias.index', compact('grados'));
        } catch (\Exception $e) {
            return redirect()->route('grados.index')
                ->with('error', 'Error al cargar la lista de grados: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $grado_materium)
    {
        try {
            $grado = Grado::with('materias')->findOrFail($grado_materium);
            return view('grados.materias.show', compact('grado'));
        } catch (\Exception $e) {
            return redirect()->route('grado-materia.index')
                ->with('error', 'Error al cargar las materias del grado: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $grado_materium)
    {
        try {
            $grado = Grado::findOrFail($grado_materium);
            $nivel = strtolower($grado->nivel);
            $materias = Materia::where('nivel', $nivel)
                ->orderBy('nombre')
                ->get();
            return view('grados.materias.edit', compact('grado', 'materias'));
        } catch (\Exception $e) {
            return redirect()->route('grado-materia.index')
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $grado_materium)
    {
        try {
            $request->validate([
                'materia_ids' => 'required|array',
                'materia_ids.*' => 'exists:materias,id'
            ]);

            $grado = Grado::findOrFail($grado_materium);
            $grado->materias()->sync($request->materia_ids);

            return redirect()->route('grado-materia.show', $grado_materium)
                ->with('success', 'Materias actualizadas exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('grado-materia.edit', $grado_materium)
                ->with('error', 'Error al actualizar las materias: ' . $e->getMessage());
        }
    }
}
