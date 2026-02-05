<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfesorController extends Controller
{
    private const TIPOS_VALIDOS_PROFESOR = ['profesor', 'profesor_extracurricular'];

    /**
     * Muestra la lista de profesores
     */
    public function index()
    {
        try {
            $profesores = Profesor::with('user')->orderBy('user_id')->get();
            $users = User::whereIn('tipo', self::TIPOS_VALIDOS_PROFESOR)->get();

            if ($profesores === null) {
                $profesores = collect([]);
            }
            if ($users === null) {
                $users = collect([]);
            }

            return view('profesores.index', [
                'profesores' => $profesores,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return view('profesores.index')->with('error', 'Error al cargar los profesores: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para crear un nuevo profesor
     */
    public function create()
    {
        $users = User::whereIn('tipo', self::TIPOS_VALIDOS_PROFESOR)->get();
        return view('profesores.create', compact('users'));
    }

    /**
     * Almacena un nuevo profesor
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'codigo_profesor' => 'required|string|max:255|unique:profesores',
                'especialidad' => 'required|string|max:255',
                'fecha_contratacion' => 'required|date',
                'tipo_contrato' => 'required|in:titular,contratado,sustituto'
            ]);

            $user = User::findOrFail($validated['user_id']);
            if (!in_array($user->tipo, self::TIPOS_VALIDOS_PROFESOR, true)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'El usuario seleccionado no es de tipo profesor/profesor extracurricular');
            }

            $profesor = new Profesor();
            $profesor->user_id = $validated['user_id'];
            $profesor->codigo_profesor = $validated['codigo_profesor'];
            $profesor->especialidad = $validated['especialidad'];
            $profesor->fecha_contratacion = $validated['fecha_contratacion'];
            $profesor->tipo_contrato = $validated['tipo_contrato'];
            $profesor->save();

            return redirect()->route('profesores.index')
                ->with('success', 'Profesor creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el profesor: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los detalles de un profesor
     */
    public function show(Profesor $profesor)
    {
        return view('profesores.show', compact('profesor'));
    }

    /**
     * Muestra el formulario para editar un profesor
     */
    public function edit(Profesor $profesor)
    {
        return view('profesores.edit', compact('profesor'));
    }

    /**
     * Actualiza un profesor
     */
    public function update(Request $request, Profesor $profesor)
    {
        $validated = $request->validate([
            'codigo_profesor' => 'required|string|max:255|unique:profesores,codigo_profesor,' . $profesor->id,
            'especialidad' => 'required|string|max:255',
            'fecha_contratacion' => 'required|date',
            'tipo_contrato' => 'required|in:titular,contratado,sustituto'
        ], [], [
            'user_id' => 'usuario'
        ]);

        $validated['user_id'] = $profesor->user_id;

        try {
            $profesor->update($validated);
            return redirect()->route('profesores.index')
                ->with('success', 'Profesor actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el profesor: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un profesor
     */
    public function destroy(Profesor $profesor)
    {
        try {
            $profesor->delete();
            return redirect()->route('profesores.index')
                ->with('success', 'Profesor eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el profesor: ' . $e->getMessage());
        }
    }
}
