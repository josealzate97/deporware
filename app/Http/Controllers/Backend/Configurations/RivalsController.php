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
        $search = trim((string) request()->query('search', ''));
        $status = (string) request()->query('status', '');
        $statusOptions = [
            (string) RivalTeam::ACTIVE => 'Activos',
            (string) RivalTeam::INACTIVE => 'Inactivos',
        ];

        if ($status !== '' && !array_key_exists($status, $statusOptions)) {
            $status = '';
        }

        $rivalsQuery = RivalTeam::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ;

        if ($search !== '') {
            $rivalsQuery->where('name', 'like', '%' . $search . '%');
        }

        if ($status !== '') {
            $rivalsQuery->where('status', (int) $status);
        }

        $rivals = $rivalsQuery->paginate(10)->withQueryString();

        return view('backend.configurations.rivals.index', [
            'rivals' => $rivals,
            'search' => $search,
            'statusOptions' => $statusOptions,
            'selectedStatus' => $status,
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

        $rival->update(['status' => RivalTeam::INACTIVE]);

        return redirect()->route('configurations.rivals.index');
    }

    public function activate(string $id)
    {
        $rival = RivalTeam::findOrFail($id);

        $rival->update(['status' => RivalTeam::ACTIVE]);

        return redirect()->route('configurations.rivals.index');
    }
}
