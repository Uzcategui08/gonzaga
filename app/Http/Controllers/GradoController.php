<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $grados = Grado::orderBy('created_at', 'asc')->get();
            return view('grados.index', compact('grados'));
        } catch (\Exception $e) {
            return view('grados.index')->with('error', 'Error al cargar los grados: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('grados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|string|max:255',
        ]);

        Grado::create($request->all());

        return redirect()->route('grados.index')
            ->with('success', 'Grado creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grado $grado)
    {
        return view('grados.show', compact('grado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grado $grado)
    {
        return view('grados.edit', compact('grado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grado $grado)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|string|max:255',
        ]);

        $grado->update($request->all());

        return redirect()->route('grados.index')
            ->with('success', 'Grado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grado $grado)
    {
        $grado->delete();

        return redirect()->route('grados.index')
            ->with('success', 'Grado eliminado exitosamente.');
    }
}
