<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamsController extends Controller
{   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $typeOptions = Team::typeOptions();
        $activeType = request()->query('type', 'competitive');
        if (!array_key_exists($activeType, $typeOptions)) {
            $activeType = 'competitive';
        }

        $seasonFilter = trim((string) request()->query('season', ''));
        $yearFilter = trim((string) request()->query('year', ''));

        $teamsQuery = Team::query()
            ->where('type', $typeOptions[$activeType])
            ->orderByDesc('status')
            ->orderBy('name');

        if ($seasonFilter !== '') {
            $teamsQuery->where('season', 'like', '%' . $seasonFilter . '%');
        }

        if ($yearFilter !== '') {
            $teamsQuery->where('year', $yearFilter);
        }

        $teams = $teamsQuery->get();

        return view('backend.teams.index', [
            'teams' => $teams,
            'activeType' => $activeType,
            'statusOptions' => [
                '1' => 'Activas',
                '0' => 'Inactivas',
            ],
            'seasonFilter' => $seasonFilter,
            'yearFilter' => $yearFilter,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('backend.teams.new', [
            'isEdit' => false,
            'team' => new Team(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|string|max:4',
            'type' => 'required|integer|in:1,2',
            'season' => 'required|string|max:20',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        Team::create($data);

        $redirectType = ((int) $data['type'] === Team::TYPE_FORMATIVE) ? 'formative' : 'competitive';

        return redirect()->route('teams.index', ['type' => $redirectType]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $team = Team::findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.teams.show-modal', [
                'team' => $team,
            ]);
        }

        return redirect()->route('teams.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        return view('backend.teams.new', [
            'isEdit' => true,
            'team' => Team::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|string|max:4',
            'type' => 'required|integer|in:1,2',
            'season' => 'required|string|max:20',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        $team = Team::findOrFail($id);
        $team->update($data);

        $redirectType = ((int) $data['type'] === Team::TYPE_FORMATIVE) ? 'formative' : 'competitive';

        return redirect()->route('teams.index', ['type' => $redirectType]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        $team->status = false;
        $team->save();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Plantilla marcada como inactiva.',
                'team' => $team,
            ]);
        }

        return redirect()->route('teams.index');
    }

    /**
     * Activate a team.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        $team = Team::findOrFail($id);
        $team->status = true;
        $team->save();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Plantilla activada.',
                'team' => $team,
            ]);
        }

        return redirect()->route('teams.index');
    }
}
