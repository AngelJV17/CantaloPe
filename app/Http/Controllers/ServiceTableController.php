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
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%");
                });
            })
            ->orderBy('identifier', 'asc')
            ->paginate($request->perPage ?? 12)
            ->withQueryString();

        return Inertia::render('Tables/Index', [
            'tables'   => $tables,
            'filters'  => $request->only(['search', 'perPage']),
            'settings' => $user->settings,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:20'],
            'name'       => ['nullable', 'string', 'max:50'],
        ]);

        $user->serviceTables()->create([
            'identifier' => $validated['identifier'],
            'name'       => $validated['name'] ?? null,
            'uuid'       => (string) Str::uuid(),
            'status'     => 'available',
            'is_active'  => true,
        ]);

        return back()->with('success', 'Mesa creada con éxito.');
    }

    public function update(Request $request, ServiceTable $table)
    {
        abort_unless($table->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:20'],
            'name'       => ['nullable', 'string', 'max:50'],
            'status'     => ['nullable', 'string', 'max:50'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $table->update([
            'identifier' => $validated['identifier'],
            'name'       => $validated['name'] ?? null,
            'status'     => $validated['status'] ?? $table->status,
            'is_active'  => $validated['is_active'] ?? $table->is_active,
        ]);

        return back()->with('success', 'Mesa actualizada correctamente.');
    }

    public function destroy(ServiceTable $table)
    {
        abort_unless($table->user_id === Auth::id(), 403);

        $table->delete();

        return back()->with('success', 'Mesa eliminada del sistema.');
    }
}
