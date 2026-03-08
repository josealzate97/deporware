<?php

namespace App\Http\Controllers\Backend\Configurations;

use App\Http\Controllers\Controller;
use App\Models\RivalTeam;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RivalsController extends Controller
{
    public function index()
    {
        $rivals = RivalTeam::query()
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('backend.configurations.rivals.index', [
            'rivals' => $rivals,
        ]);
    }

    public function create()
    {
        return view('backend.configurations.rivals.form', [
            'isEdit' => false,
            'rival' => new RivalTeam(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:250|unique:rival_teams,name',
        ]);

        RivalTeam::create($data);

        return redirect()->route('configurations.rivals.index');
    }

    public function edit(string $id)
    {
        return view('backend.configurations.rivals.form', [
            'isEdit' => true,
            'rival' => RivalTeam::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $rival = RivalTeam::findOrFail($id);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:250',
                Rule::unique('rival_teams', 'name')->ignore($rival->id),
            ],
        ]);

        $rival->update($data);

        return redirect()->route('configurations.rivals.index');
    }

    public function destroy(string $id)
    {
        $rival = RivalTeam::findOrFail($id);

        if ($rival->matches()->exists()) {
            return redirect()->route('configurations.rivals.index')
                ->with('error', 'No puedes eliminar un rival con partidos asociados.');
        }

        $rival->delete();

        return redirect()->route('configurations.rivals.index');
    }
}
