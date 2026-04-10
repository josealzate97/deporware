<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $tenants = Tenant::withCount(['users', 'teams', 'players'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->orderBy('number')
            ->paginate(10)
            ->withQueryString();

        return view('backend.tenants.index', compact('tenants', 'search'));
    }

    public function create()
    {
        return view('backend.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:120',
            'status' => 'required|in:0,1',
        ]);

        Tenant::create($data);

        return redirect()->route('tenants.index')
            ->with('success', 'Escuela creada correctamente.');
    }

    public function edit(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('backend.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $data = $request->validate([
            'name'   => 'required|string|max:120',
            'status' => 'required|in:0,1',
        ]);

        $tenant->update($data);

        return redirect()->route('tenants.index')
            ->with('success', 'Escuela actualizada correctamente.');
    }

    public function activate(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => $tenant->status === Tenant::ACTIVE ? Tenant::INACTIVE : Tenant::ACTIVE]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->users()->exists() || $tenant->teams()->exists()) {
            return back()->with('error', 'No se puede eliminar una escuela con usuarios o plantillas registradas.');
        }

        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', 'Escuela eliminada.');
    }
}
