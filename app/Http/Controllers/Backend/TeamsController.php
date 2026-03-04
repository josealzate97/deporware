<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\User;
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
            'venues' => SportsVenue::where('status', true)->orderBy('name')->get(),
            'teamVenueIds' => [],
            'coaches' => User::where('role', User::ROLE_COACH)->orderBy('name')->get(),
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
            'venues' => 'nullable|array',
            'venues.*' => 'uuid|exists:sports_venues,id',
        ]);

        $data['status'] = $request->boolean('status');

        $venueIds = $data['venues'] ?? [];
        unset($data['venues']);

        $team = Team::create($data);

        if (!empty($venueIds)) {
            $team->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        }

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
        $team = Team::with('venues')->findOrFail($id);

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
        $team = Team::with('venues')->findOrFail($id);

        return view('backend.teams.new', [
            'isEdit' => true,
            'team' => $team,
            'venues' => SportsVenue::orderBy('name')->get(),
            'teamVenueIds' => $team->venues->pluck('id')->all(),
            'coaches' => User::where('role', User::ROLE_COACH)->orderBy('name')->get(),
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
            'venues' => 'nullable|array',
            'venues.*' => 'uuid|exists:sports_venues,id',
        ]);

        $data['status'] = $request->boolean('status');

        $venueIds = $data['venues'] ?? [];
        unset($data['venues']);

        $team = Team::findOrFail($id);
        $team->update($data);

        if (!empty($venueIds)) {
            $team->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        } else {
            $team->venues()->detach();
        }

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
