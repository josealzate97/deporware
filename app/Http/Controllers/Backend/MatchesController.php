<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AttackPoint;
use App\Models\DefensivePoint;
use App\Models\MatchFeedback;
use App\Models\MatchModel;
use App\Models\MatchTeamRating;
use App\Models\RivalTeam;
use App\Models\SportsVenue;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchesController extends Controller
{   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $search = request('search');
        $status = request('status');
        $team = request('team');
        $rival = request('rival');

        $matchesQuery = MatchModel::with(['team', 'rival'])
            ->when($search, function ($query, $searchTerm) {
                $query->where('match_date', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('team', function ($teamQuery) use ($searchTerm) {
                        $teamQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('rival', function ($rivalQuery) use ($searchTerm) {
                        $rivalQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('match_status', $status);
            })
            ->when($team, function ($query) use ($team) {
                $query->where('team', $team);
            })
            ->when($rival, function ($query) use ($rival) {
                $query->where('rival', $rival);
            })
            ->orderByDesc('match_date');

        $matches = $matchesQuery->paginate(10)->withQueryString();

        return view('backend.matches.index', [
            'matches' => $matches,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedTeam' => $team,
            'selectedRival' => $rival,
            'statusOptions' => MatchModel::statusOptions(),
            'teamOptions' => Team::orderBy('name')->pluck('name', 'id'),
            'rivalOptions' => RivalTeam::orderBy('name')->pluck('name', 'id'),
            'selectedTeamName' => $team ? (Team::whereKey($team)->value('name') ?? '') : '',
            'selectedRivalName' => $rival ? (RivalTeam::whereKey($rival)->value('name') ?? '') : '',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('backend.matches.new', [
            'isEdit' => false,
            'teams' => Team::orderBy('name')->get(),
            'rivals' => RivalTeam::orderBy('name')->get(),
            'venues' => SportsVenue::orderBy('name')->get(),
            'attackPoints' => AttackPoint::orderBy('name')->get(),
            'defensivePoints' => DefensivePoint::orderBy('name')->get(),
            'statusOptions' => MatchModel::statusOptions(),
            'resultOptions' => MatchModel::resultOptions(),
            'sideOptions' => MatchModel::sideOptions(),
            'formationOptions' => MatchModel::formationOptions(),
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
        $statusCompleted = MatchModel::STATUS_COMPLETED;
        $statusScheduled = MatchModel::STATUS_SCHEDULED;
        $isCompleted = (int) $request->input('match_status') === $statusCompleted;
        $isScheduled = (int) $request->input('match_status') === $statusScheduled;

        $validated = $request->validate([
            'match_date' => ['required', 'date'],
            'match_round' => ['nullable', 'string', 'max:50'],
            'team' => ['required', 'uuid', 'exists:teams,id'],
            'rival' => ['required', 'uuid', 'exists:rival_teams,id'],
            'venue' => ['nullable', 'uuid', 'exists:sports_venues,id'],
            'location' => ['nullable', 'string', 'max:250'],
            'side' => ['required', 'integer'],
            'match_status' => ['required', 'integer'],
            'match_result' => [$isScheduled ? 'nullable' : 'required', 'integer'],
            'final_score' => [$isScheduled ? 'nullable' : 'required', 'string', 'max:20'],
            'match_notes' => ['nullable', 'string'],
            'match_file' => [$isScheduled ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            'team_photo' => ['nullable', 'image'],

            'match_feedback.match_formation' => [$isCompleted ? 'required' : 'nullable', 'string', 'max:20'],
            'match_feedback.attack_strengths' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:attack_points,id'],
            'match_feedback.attack_weaknesses' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:attack_points,id'],
            'match_feedback.defense_strengths' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:defensive_points,id'],
            'match_feedback.defense_weaknesses' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:defensive_points,id'],
            'match_feedback.notes' => ['nullable', 'string'],

            'match_team_rating.referee_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.coach_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.teammates_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.opponents_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.fans_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request, $isCompleted) {
            $matchData = [
                'match_date' => $validated['match_date'],
                'match_round' => $validated['match_round'] ?? null,
                'team' => $validated['team'],
                'rival' => $validated['rival'],
                'venue' => $validated['venue'] ?? null,
                'location' => $validated['location'] ?? null,
                'side' => $validated['side'],
                'match_status' => $validated['match_status'],
                'match_result' => $validated['match_result'] ?? null,
                'final_score' => $validated['final_score'] ?? null,
                'match_notes' => $validated['match_notes'] ?? null,
            ];

            if ($request->hasFile('match_file')) {
                $matchData['match_file'] = file_get_contents($request->file('match_file')->getRealPath());
            }

            if ($request->hasFile('team_photo')) {
                $matchData['team_picture'] = file_get_contents($request->file('team_photo')->getRealPath());
            }

            $match = MatchModel::create($matchData);

            if ($isCompleted) {
                $feedback = $validated['match_feedback'] ?? [];
                MatchFeedback::create([
                    'match' => $match->id,
                    'match_formation' => $feedback['match_formation'],
                    'attack_strengths' => $feedback['attack_strengths'],
                    'attack_weaknesses' => $feedback['attack_weaknesses'],
                    'defense_strengths' => $feedback['defense_strengths'],
                    'defense_weaknesses' => $feedback['defense_weaknesses'],
                    'notes' => $feedback['notes'] ?? null,
                ]);

                $rating = $validated['match_team_rating'] ?? [];
                MatchTeamRating::create([
                    'match' => $match->id,
                    'referee_rating' => $rating['referee_rating'],
                    'coach_rating' => $rating['coach_rating'],
                    'teammates_rating' => $rating['teammates_rating'],
                    'opponents_rating' => $rating['opponents_rating'],
                    'fans_rating' => $rating['fans_rating'],
                    'notes' => $rating['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('matches.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $match = MatchModel::with(['team', 'rival', 'venue'])->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.matches.show-modal', [
                'match' => $match,
            ]);
        }

        return redirect()->route('matches.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $match = MatchModel::with(['feedback', 'teamRating'])->findOrFail($id);

        return view('backend.matches.new', [
            'isEdit' => true,
            'match' => $match,
            'teams' => Team::orderBy('name')->get(),
            'rivals' => RivalTeam::orderBy('name')->get(),
            'venues' => SportsVenue::orderBy('name')->get(),
            'attackPoints' => AttackPoint::orderBy('name')->get(),
            'defensivePoints' => DefensivePoint::orderBy('name')->get(),
            'statusOptions' => MatchModel::statusOptions(),
            'resultOptions' => MatchModel::resultOptions(),
            'sideOptions' => MatchModel::sideOptions(),
            'formationOptions' => MatchModel::formationOptions(),
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
        $match = MatchModel::with(['feedback', 'teamRating'])->findOrFail($id);

        $statusCompleted = MatchModel::STATUS_COMPLETED;
        $statusScheduled = MatchModel::STATUS_SCHEDULED;
        $isCompleted = (int) $request->input('match_status') === $statusCompleted;
        $isScheduled = (int) $request->input('match_status') === $statusScheduled;

        $validated = $request->validate([
            'match_date' => ['required', 'date'],
            'match_round' => ['nullable', 'string', 'max:50'],
            'team' => ['required', 'uuid', 'exists:teams,id'],
            'rival' => ['required', 'uuid', 'exists:rival_teams,id'],
            'venue' => ['nullable', 'uuid', 'exists:sports_venues,id'],
            'location' => ['nullable', 'string', 'max:250'],
            'side' => ['required', 'integer'],
            'match_status' => ['required', 'integer'],
            'match_result' => [$isScheduled ? 'nullable' : 'required', 'integer'],
            'final_score' => [$isScheduled ? 'nullable' : 'required', 'string', 'max:20'],
            'match_notes' => ['nullable', 'string'],
            'match_file' => [$isScheduled ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            'team_photo' => ['nullable', 'image'],

            'match_feedback.match_formation' => [$isCompleted ? 'required' : 'nullable', 'string', 'max:20'],
            'match_feedback.attack_strengths' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:attack_points,id'],
            'match_feedback.attack_weaknesses' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:attack_points,id'],
            'match_feedback.defense_strengths' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:defensive_points,id'],
            'match_feedback.defense_weaknesses' => [$isCompleted ? 'required' : 'nullable', 'uuid', 'exists:defensive_points,id'],
            'match_feedback.notes' => ['nullable', 'string'],

            'match_team_rating.referee_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.coach_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.teammates_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.opponents_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.fans_rating' => [$isCompleted ? 'required' : 'nullable', 'integer', 'between:1,10'],
            'match_team_rating.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request, $match, $isCompleted) {
            $matchData = [
                'match_date' => $validated['match_date'],
                'match_round' => $validated['match_round'] ?? null,
                'team' => $validated['team'],
                'rival' => $validated['rival'],
                'venue' => $validated['venue'] ?? null,
                'location' => $validated['location'] ?? null,
                'side' => $validated['side'],
                'match_status' => $validated['match_status'],
                'match_result' => $validated['match_result'] ?? null,
                'final_score' => $validated['final_score'] ?? null,
                'match_notes' => $validated['match_notes'] ?? null,
            ];

            if ($request->hasFile('match_file')) {
                $matchData['match_file'] = file_get_contents($request->file('match_file')->getRealPath());
            }

            if ($request->hasFile('team_photo')) {
                $matchData['team_picture'] = file_get_contents($request->file('team_photo')->getRealPath());
            }

            $match->update($matchData);

            if ($isCompleted) {
                $feedback = $validated['match_feedback'] ?? [];
                MatchFeedback::updateOrCreate(
                    ['match' => $match->id],
                    [
                        'match_formation' => $feedback['match_formation'],
                        'attack_strengths' => $feedback['attack_strengths'],
                        'attack_weaknesses' => $feedback['attack_weaknesses'],
                        'defense_strengths' => $feedback['defense_strengths'],
                        'defense_weaknesses' => $feedback['defense_weaknesses'],
                        'notes' => $feedback['notes'] ?? null,
                    ]
                );

                $rating = $validated['match_team_rating'] ?? [];
                MatchTeamRating::updateOrCreate(
                    ['match' => $match->id],
                    [
                        'referee_rating' => $rating['referee_rating'],
                        'coach_rating' => $rating['coach_rating'],
                        'teammates_rating' => $rating['teammates_rating'],
                        'opponents_rating' => $rating['opponents_rating'],
                        'fans_rating' => $rating['fans_rating'],
                        'notes' => $rating['notes'] ?? null,
                    ]
                );
            } else {
                $match->feedback()->delete();
                $match->teamRating()->delete();
            }
        });

        return redirect()->route('matches.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        return redirect()->route('matches.index');
    }
}
