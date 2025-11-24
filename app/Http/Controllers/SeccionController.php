<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $profesores = \App\Models\Profesor::with('user')->join('users', 'profesores.user_id', '=', 'users.id')->orderBy('users.name')->get(['profesores.*']);
        return view('secciones.create', compact('grados', 'profesores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'grado_id' => 'required|exists:grados,id',
            'titular_profesor_id' => 'nullable|exists:profesores,id'
        ]);

        try {
            $data = $request->only(['nombre', 'grado_id', 'titular_profesor_id']);
            $data['titular_profesor_id'] = $data['titular_profesor_id'] ?: null;
            Seccion::create($data);
            return redirect()->route('secciones.index')
                ->with('success', 'Sección creada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error creando sección', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error al crear la sección: ' . $e->getMessage());
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
        $profesores = \App\Models\Profesor::with('user')->join('users', 'profesores.user_id', '=', 'users.id')->orderBy('users.name')->get(['profesores.*']);
        return view('secciones.edit', compact('seccion', 'grados', 'profesores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seccion $seccion)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'grado_id' => 'required|exists:grados,id',
            'titular_profesor_id' => 'nullable|exists:profesores,id'
        ]);

        try {
            $data = $request->only(['nombre', 'grado_id', 'titular_profesor_id']);
            $data['titular_profesor_id'] = $data['titular_profesor_id'] ?: null;
            $seccion->update($data);
            return redirect()->route('secciones.index')
                ->with('success', 'Sección actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error actualizando sección', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error al actualizar la sección: ' . $e->getMessage());
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

    /**
     * API: Listar secciones por nivel de grado (e.g., 'primaria', 'secundaria').
     */
    public function seccionesPorNivel(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'nivel' => 'required|string'
        ]);

        try {
            $nivel = strtolower($request->get('nivel'));
            $secciones = Seccion::with('grado')
                ->whereHas('grado', function ($q) use ($nivel) {
                    $q->whereRaw('LOWER(nivel) = ?', [$nivel]);
                })
                ->orderBy('nombre')
                ->get()
                ->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'grado' => $s->grado?->nombre,
                        'nivel' => $s->grado?->nivel,
                    ];
                });

            return response()->json([
                'success' => true,
                'secciones' => $secciones,
                'requested_nivel' => $nivel,
                'total' => $secciones->count()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
