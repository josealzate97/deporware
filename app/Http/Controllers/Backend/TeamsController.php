<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ManagerRoster;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'coachPrimaryId' => null,
            'coachSecondaryId' => null,
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
            'coach_primary' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'id')->where('role', User::ROLE_COACH),
            ],
            'coach_secondary' => [
                'nullable',
                'uuid',
                'different:coach_primary',
                Rule::exists('users', 'id')->where('role', User::ROLE_COACH),
            ],
        ]);

        $data['status'] = $request->boolean('status');

        $venueIds = $data['venues'] ?? [];
        unset($data['venues']);

        $coachPrimaryId = $data['coach_primary'] ?? null;
        $coachSecondaryId = $data['coach_secondary'] ?? null;
        unset($data['coach_primary'], $data['coach_secondary']);

        $team = Team::create($data);

        if (!empty($venueIds)) {
            $team->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        }

        $this->syncTeamCoaches($team, $coachPrimaryId, $coachSecondaryId);

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
        $team = Team::with(['venues', 'managerRosters.user'])->findOrFail($id);
        $primaryCoachId = optional(
            $team->managerRosters->firstWhere('role', ManagerRoster::ROLE_PRIMARY_COACH)
        )->user;
        $assistantCoachId = optional(
            $team->managerRosters->firstWhere('role', ManagerRoster::ROLE_ASSISTANT_COACH)
        )->user;
        $primaryCoach = $primaryCoachId ? User::find($primaryCoachId) : null;
        $assistantCoach = $assistantCoachId ? User::find($assistantCoachId) : null;

        if (request()->boolean('modal')) {
            return view('backend.teams.show-modal', [
                'team' => $team,
                'primaryCoach' => $primaryCoach,
                'assistantCoach' => $assistantCoach,
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
        $team = Team::with(['venues', 'managerRosters'])->findOrFail($id);
        $coachPrimaryId = optional(
            $team->managerRosters->firstWhere('role', ManagerRoster::ROLE_PRIMARY_COACH)
        )->user;
        $coachSecondaryId = optional(
            $team->managerRosters->firstWhere('role', ManagerRoster::ROLE_ASSISTANT_COACH)
        )->user;

        return view('backend.teams.new', [
            'isEdit' => true,
            'team' => $team,
            'venues' => SportsVenue::where('status', true)->orderBy('name')->get(),
            'teamVenueIds' => $team->venues->pluck('id')->all(),
            'coaches' => User::where('role', User::ROLE_COACH)->orderBy('name')->get(),
            'coachPrimaryId' => $coachPrimaryId,
            'coachSecondaryId' => $coachSecondaryId,
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
            'coach_primary' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'id')->where('role', User::ROLE_COACH),
            ],
            'coach_secondary' => [
                'nullable',
                'uuid',
                'different:coach_primary',
                Rule::exists('users', 'id')->where('role', User::ROLE_COACH),
            ],
        ]);

        $data['status'] = $request->boolean('status');

        $venueIds = $data['venues'] ?? [];
        unset($data['venues']);

        $coachPrimaryId = $data['coach_primary'] ?? null;
        $coachSecondaryId = $data['coach_secondary'] ?? null;
        unset($data['coach_primary'], $data['coach_secondary']);

        $team = Team::findOrFail($id);
        $team->update($data);

        if (!empty($venueIds)) {
            $team->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        } else {
            $team->venues()->detach();
        }

        $this->syncTeamCoaches($team, $coachPrimaryId, $coachSecondaryId);

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

    private function syncTeamCoaches(Team $team, ?string $coachPrimaryId, ?string $coachSecondaryId): void
    {
        ManagerRoster::where('team', $team->id)
            ->whereIn('role', [ManagerRoster::ROLE_PRIMARY_COACH, ManagerRoster::ROLE_ASSISTANT_COACH])
            ->delete();

        if (!empty($coachPrimaryId)) {
            ManagerRoster::create([
                'user' => $coachPrimaryId,
                'team' => $team->id,
                'role' => ManagerRoster::ROLE_PRIMARY_COACH,
                'status' => 1,
            ]);
        }

        if (!empty($coachSecondaryId)) {
            ManagerRoster::create([
                'user' => $coachSecondaryId,
                'team' => $team->id,
                'role' => ManagerRoster::ROLE_ASSISTANT_COACH,
                'status' => 1,
            ]);
        }
    }
}
