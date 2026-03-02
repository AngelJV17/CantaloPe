<?php
namespace App\Http\Controllers;

use App\Models\ServiceTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ServiceTableController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $tables = ServiceTable::where('user_id', $user->id)
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('identifier', 'like', "%{$search}%");
                });
            })
            ->orderBy('identifier', 'asc')
            ->paginate($request->perPage ?? 12)
            ->withQueryString(); // Mantiene los filtros al cambiar de página

        return Inertia::render('Tables/Index', [
            'tables'   => $tables,
            'filters'  => $request->only(['search', 'perPage']),
            'settings' => $user->shopSettings, 
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:50',
            // El identificador es único por usuario
            'identifier' => 'required|string|max:20',
            'zone'       => 'nullable|string',
            'capacity'   => 'nullable|integer',
        ]);

        Auth::user()->serviceTables()->create([
            'name'       => $request->name,
            'identifier' => $request->identifier,
            'uuid'       => (string) Str::uuid(),
            'zone'       => $request->zone ?? 'General',
            'capacity'   => $request->capacity ?? 4,
        ]);

        return back()->with('success', 'Mesa creada con éxito.');
    }

    public function update(Request $request, ServiceTable $table)
    {
        // Seguridad: Verificar que la mesa pertenece al usuario
        if ($table->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta mesa.');
        }

        $request->validate([
            'name'       => 'required|string|max:50',
            'identifier' => 'required|string|max:20',
            'zone'       => 'nullable|string',
            'capacity'   => 'nullable|integer',
        ]);

        $table->update($request->only(['name', 'identifier', 'zone', 'capacity']));

        return back()->with('success', 'Mesa actualizada correctamente.');
    }

    public function destroy(ServiceTable $table)
    {
        if ($table->user_id !== Auth::id()) {
            abort(403);
        }

        $table->delete();

        return back()->with('success', 'Mesa eliminada del sistema.');
    }
}
