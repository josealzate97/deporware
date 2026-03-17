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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MatchesController extends Controller
{   
    private bool $storageErrorShown = false;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $view = request('view', 'list');
        if (!in_array($view, ['list', 'calendar'], true)) {
            $view = 'list';
        }

        $search = request('search');
        $status = request('status');
        $team = request('team');
        $rival = request('rival');
        $month = request('month');

        $monthStart = Carbon::now()->startOfMonth();
        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            try {
                $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            } catch (\Throwable $e) {
                $monthStart = Carbon::now()->startOfMonth();
            }
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        $matchesQuery = MatchModel::with(['team', 'rival', 'feedback', 'teamRating'])

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
        });

        $matches = (clone $matchesQuery)
            ->orderByDesc('match_date')
            ->paginate(10)
            ->withQueryString();

        $calendarMatches = (clone $matchesQuery)
            ->whereBetween('match_date', [$monthStart, $monthEnd])
            ->orderBy('match_date')
            ->get();

        $calendarMatchesData = $calendarMatches->map(function (MatchModel $match) {
            $teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null;
            $rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null;
            return [
                'id' => $match->id,
                'date' => $match->match_date?->format('Y-m-d'),
                'time' => $match->match_date?->format('H:i') ?? '-',
                'team' => $teamModel?->name ?? ($match->team ? 'Sin equipo vinculado' : 'Sin equipo'),
                'rival' => $rivalModel?->name ?? ($match->rival ? 'Sin rival vinculado' : 'Sin rival'),
                'statusCode' => (int) $match->match_status,
                'status' => MatchModel::statusOptions()[$match->match_status] ?? 'Sin estado',
                'resultCode' => $match->match_result ? (int) $match->match_result : null,
                'resultLabel' => MatchModel::resultOptions()[$match->match_result] ?? null,
                'score' => $match->final_score ?: '-',
            ];
        })->filter(fn ($item) => !empty($item['date']))->values();

        return view('backend.matches.index', [
            'activeView' => $view,
            'matches' => $matches,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedTeam' => $team,
            'selectedRival' => $rival,
            'calendarMonth' => $monthStart->format('Y-m'),
            'calendarMonthLabel' => ucfirst($monthStart->locale('es')->isoFormat('MMMM [de] YYYY')),
            'calendarMatchesData' => $calendarMatchesData,
            'statusOptions' => MatchModel::statusOptions(),
            'resultOptions' => MatchModel::resultOptions(),
            'sideOptions' => MatchModel::sideOptions(),
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
            'match_file' => [$isScheduled ? 'nullable' : 'required', 'file', 'mimes:pdf,docx,xls,xlsx'],
            'team_photo' => ['nullable', 'mimes:jpg,jpeg,png'],

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

            $match = MatchModel::create($matchData);
            $this->ensureMatchStorage($match->id, $match->team);
            $this->storeMatchFiles($match, $request);

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
        $match = MatchModel::with([
            'team',
            'rival',
            'venue',
            'feedback.attackStrength',
            'feedback.attackWeakness',
            'feedback.defenseStrength',
            'feedback.defenseWeakness',
            'teamRating',
        ])->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.matches.show-modal', [
                'match' => $match,
                'statusOptions' => MatchModel::statusOptions(),
                'resultOptions' => MatchModel::resultOptions(),
                'sideOptions' => MatchModel::sideOptions(),
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
        $removingMatchFile = $request->boolean('remove_match_file');
        $requiresMatchFile = !$isScheduled && ($removingMatchFile || empty($match->match_file)) && !$request->hasFile('match_file');

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
            'match_file' => [$requiresMatchFile ? 'required' : 'nullable', 'file', 'mimes:pdf,docx,xls,xlsx'],
            'team_photo' => ['nullable', 'mimes:jpg,jpeg,png'],
            'remove_match_file' => ['nullable', 'boolean'],
            'remove_team_photo' => ['nullable', 'boolean'],

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

            $previousTeamId = $match->team;

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

            if ($request->boolean('remove_match_file')) {
                $this->removeStoredMatchAsset($match, 'match_file');
            }

            if ($request->boolean('remove_team_photo')) {
                $this->removeStoredMatchAsset($match, 'team_picture');
            }

            $match->update($matchData);
            $this->syncMatchStorage($match->id, $match->team, $previousTeamId);
            $this->syncMatchFilePaths($match, $previousTeamId);
            $this->storeMatchFiles($match, $request);

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

    public function downloadReport($id)
    {
        $match = MatchModel::findOrFail($id);
        $path = $match->match_file;

        if (empty($path) || !Storage::disk('public')->exists($path)) {
            return back()->with('error', 'No hay informe disponible para descargar.');
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
        $fileName = 'informe-partido-' . $match->id . '.' . $extension;

        return response()->download(storage_path('app/public/' . $path), $fileName);
    }

    public function viewReport($id)
    {
        $match = MatchModel::findOrFail($id);

        return $this->streamMatchAssetInline($match->match_file, 'No hay informe disponible para visualizar.');
    }

    public function downloadTeamPhoto($id)
    {
        $match = MatchModel::findOrFail($id);
        $path = $match->team_picture;

        if (empty($path) || !Storage::disk('public')->exists($path)) {
            return back()->with('error', 'No hay foto de equipo disponible para descargar.');
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $fileName = 'foto-equipo-' . $match->id . '.' . $extension;

        return response()->download(storage_path('app/public/' . $path), $fileName);
    }

    public function viewTeamPhoto($id)
    {
        $match = MatchModel::findOrFail($id);

        return $this->streamMatchAssetInline($match->team_picture, 'No hay foto de equipo disponible para visualizar.');
    }

    /** 
     * Ensure that the storage directories for a match exist. If the team ID is empty, it will skip the operation. 
     * If the storage path is not writable, it will log an error. If any of the required directories cannot be created,
     *  it will log an error for each failure. This method is used when creating a new match to ensure that the necessary 
     * storage structure is in place for storing files related to the match.
     * @param string $matchId The ID of the match for which to ensure storage directories.
     * @param string|null $teamId The ID of the team associated with the match, if null or empty, 
     * the method will skip the storage creation since it cannot determine the path.
     * @return void
    */
    private function ensureMatchStorage(string $matchId, string $teamId): void
    {
        $this->ensureStorageWritable();
        $disk = Storage::disk('public');
        $basePath = "teams/{$teamId}/matches/{$matchId}";

        if (!$disk->exists($basePath) && !$disk->makeDirectory($basePath)) {

            Log::error('Failed to create match folder.', [
                'match' => $matchId,
                'team' => $teamId,
                'path' => $basePath,
            ]);

            $this->flashStorageError();
            return;
        }

        foreach (['reports', 'photos'] as $folder) {

            $fullPath = "{$basePath}/{$folder}";

            if (!$disk->exists($fullPath) && !$disk->makeDirectory($fullPath)) {
                Log::error('Failed to create match subfolder.', [
                    'path' => $fullPath,
                ]);
                $this->flashStorageError();
            }

        }
    }

    private function storeMatchFiles(MatchModel $match, Request $request): void
    {
        $disk = Storage::disk('public');
        $basePath = "teams/{$match->team}/matches/{$match->id}";

        if ($request->hasFile('match_file')) {
            $report = $request->file('match_file');
            $reportName = 'report-' . Str::uuid() . '.' . $report->getClientOriginalExtension();
            $reportPath = $disk->putFileAs("{$basePath}/reports", $report, $reportName);
            if ($reportPath) {
                if (!empty($match->match_file) && $disk->exists($match->match_file)) {
                    $disk->delete($match->match_file);
                }
                $match->update(['match_file' => $reportPath]);
            } else {
                Log::error('Failed to store match report.', ['match' => $match->id]);
                $this->flashStorageError();
            }
        }

        if ($request->hasFile('team_photo')) {
            $photo = $request->file('team_photo');
            $photoName = 'photo-' . Str::uuid() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $disk->putFileAs("{$basePath}/photos", $photo, $photoName);
            if ($photoPath) {
                if (!empty($match->team_picture) && $disk->exists($match->team_picture)) {
                    $disk->delete($match->team_picture);
                }
                $match->update(['team_picture' => $photoPath]);
            } else {
                Log::error('Failed to store match photo.', ['match' => $match->id]);
                $this->flashStorageError();
            }
        }
    }

    private function removeStoredMatchAsset(MatchModel $match, string $attribute): void
    {
        $path = $match->{$attribute};

        if (empty($path)) {
            $match->update([$attribute => null]);
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path) && !$disk->delete($path)) {
            Log::error('Failed to delete match asset.', [
                'match' => $match->id,
                'attribute' => $attribute,
                'path' => $path,
            ]);
            $this->flashStorageError();
            return;
        }

        $match->update([$attribute => null]);
    }

    private function streamMatchAssetInline(?string $path, string $missingMessage)
    {
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            return back()->with('error', $missingMessage);
        }

        $fullPath = storage_path('app/public/' . $path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function syncMatchFilePaths(MatchModel $match, ?string $previousTeamId): void
    {
        if (empty($previousTeamId) || $previousTeamId === $match->team) {
            return;
        }

        $oldPrefix = "teams/{$previousTeamId}/matches/{$match->id}/";
        $newPrefix = "teams/{$match->team}/matches/{$match->id}/";

        $updates = [];
        if (!empty($match->match_file) && Str::startsWith($match->match_file, $oldPrefix)) {
            $updates['match_file'] = Str::replaceFirst($oldPrefix, $newPrefix, $match->match_file);
        }
        if (!empty($match->team_picture) && Str::startsWith($match->team_picture, $oldPrefix)) {
            $updates['team_picture'] = Str::replaceFirst($oldPrefix, $newPrefix, $match->team_picture);
        }

        if ($updates) {
            $match->update($updates);
        }
    }

    /**
     * Sync match storage when the team of a match changes.
     *
     * @param string $matchId The ID of the match.
     * @param string $teamId The ID of the new team.
     * @param string|null $previousTeamId The ID of the previous team, if any.
     * @return void
     */
    private function syncMatchStorage(string $matchId, string $teamId, ?string $previousTeamId): void
    {
        $this->ensureStorageWritable();
        $disk = Storage::disk('public');
        $newPath = "teams/{$teamId}/matches/{$matchId}";

        if (!empty($previousTeamId) && $previousTeamId !== $teamId) {

            $oldPath = "teams/{$previousTeamId}/matches/{$matchId}";

            if ($disk->exists($oldPath)) {

                if ($disk->exists($newPath)) {
                    $disk->deleteDirectory($newPath);
                }

                if (!$disk->move($oldPath, $newPath)) {
                    Log::error('Failed to move match folder to new team.', [
                        'match' => $matchId,
                        'from' => $oldPath,
                        'to' => $newPath,
                    ]);
                    $this->flashStorageError();
                }

                return;
            }
        }

        $this->ensureMatchStorage($matchId, $teamId);
    }

    /**
     * This method checks if the storage path for matches is writable. If it is not, 
     * it logs an error and flashes a message to the session to inform the user that there was an issue with storage operations. 
     * This method should be called before any operations that involve creating, moving, 
     * or deleting directories for matches to ensure that 
     * the application can handle storage issues gracefully and provide feedback to the user when necessary.
     */
    private function ensureStorageWritable(): void
    {
        $root = storage_path('app/public');

        if (!is_dir($root) || !is_writable($root)) {

            Log::error('Storage path is not writable for matches.', [
                'path' => $root,
            ]);

            $this->flashStorageError();

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $match = MatchModel::with(['feedback', 'teamRating'])->findOrFail($id);
        $this->removeMatchStorage($match->id, $match->team);

        $match->feedback()->delete();
        $match->teamRating()->delete();
        $match->delete();

        return redirect()->route('matches.index');
    }

    /** 
     * Remove match storage directory for the given match and team, if it exists.
     * If the team ID is empty, it will skip the operation. If the storage path is not writable, it will log an error and flash a message to the session.
     * If the directory exists but cannot be deleted, it will log an error and flash a message to the session.
     * This method is used when deleting a match or when changing the team of a match to ensure that old storage directories are cleaned up properly.
     * @param string $matchId The ID of the match whose storage should be removed.
     * @param string|null $teamId The ID of the team associated with the match,
     * if null or empty, the method will skip the storage removal since it cannot determine the path.
     * @return void
    */
    private function removeMatchStorage(string $matchId, ?string $teamId): void
    {
        if (empty($teamId)) {
            return;
        }

        $this->ensureStorageWritable();
        $disk = Storage::disk('public');
        $path = "teams/{$teamId}/matches/{$matchId}";

        if ($disk->exists($path) && !$disk->deleteDirectory($path)) {
            
            Log::error('Failed to delete match folder.', [
                'match' => $matchId,
                'team' => $teamId,
                'path' => $path,
            ]);

            $this->flashStorageError();

        }
    }

    /**
     * Flash a storage error message to the session.
     * This method is called whenever there is an issue with storage operations, 
     * such as when the storage path is not writable or when a directory cannot be created or deleted.
    */
    private function flashStorageError(): void
    {
        if ($this->storageErrorShown) {
            return;
        }

        $this->storageErrorShown = true;
        session()->flash('error', 'No se pudieron crear o mover carpetas de partidos. Revisa permisos de storage.');
    }
}
