<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Support\TenantStorage;
use App\Models\PlayerRoster;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\Training;
use App\Models\TrainingAttendance;
use App\Models\TrainingObservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class TrainingsController extends Controller
{   
    private bool $storageErrorShown = false;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $view = $request->input('view', 'list');
        if (!in_array($view, ['list', 'calendar'], true)) {
            $view = 'list';
        }

        $month = $request->input('month');
        $search = trim((string) $request->input('search', ''));
        $selectedStatus = $request->input('status', '');
        $selectedTeam = trim((string) $request->input('team', ''));
        $monthStart = Carbon::now()->startOfMonth();
        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            try {
                $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            } catch (\Throwable $e) {
                $monthStart = Carbon::now()->startOfMonth();
            }
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        // Scoping por rol: coordinator y coach solo ven entrenamientos de sus equipos
        $scopedTeamIds = auth()->user()->scopedTeamIds();

        $trainingsQuery = $this->accessibleTrainingsQuery(['team', 'venue'])
            ->with(['team.managerRosters.user'])
            ->withCount('observations')
            ->withCount([
                'attendance',
                'teamRosters as called_up_count' => function ($query) {
                    $query->where('status', PlayerRoster::ACTIVE)
                        ->whereHas('player', function ($playerQuery) {
                            $playerQuery->where('status', Player::ACTIVE);
                        });
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('team', function ($teamQuery) use ($search) {
                            $teamQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('venue', function ($venueQuery) use ($search) {
                            $venueQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($selectedStatus !== '' && is_numeric($selectedStatus), function ($query) use ($selectedStatus) {
                $query->where('status', (int) $selectedStatus);
            })
            ->when($selectedTeam !== '', function ($query) use ($selectedTeam) {
                $query->where('team', $selectedTeam);
            })
            ->orderByDesc('status')
            ->orderBy('name');

        $trainings = (clone $trainingsQuery)
            ->get()
            ->each(function (Training $training): void {
                $training->setAttribute('duration_label', $this->formatDurationLabel($training->duration));
            });

        $calendarTrainings = (clone $trainingsQuery)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->orderBy('created_at')
            ->get();

        $calendarTrainingsData = $calendarTrainings->map(function (Training $training) {
            $teamModel = $training->relationLoaded('team') ? $training->getRelation('team') : null;

            return [
                'id' => $training->id,
                'date' => $training->created_at?->format('Y-m-d'),
                'time' => $training->created_at?->format('H:i') ?? '-',
                'name' => $training->name ?: 'Entrenamiento',
                'team' => $teamModel?->name ?? ($training->team ? 'Sin equipo vinculado' : 'Sin equipo'),
                'statusCode' => (int) $training->status,
                'status' => (int) $training->status === Training::ACTIVE ? 'Activo' : 'Inactivo',
                'duration' => $this->formatDurationLabel($training->duration),
                'observationsCount' => (int) ($training->observations_count ?? 0),
                'editUrl' => route('trainings.edit', $training->id),
                'observationsUrl' => route('trainings.edit', ['id' => $training->id]) . '#training-observations',
            ];
        })->filter(fn ($item) => !empty($item['date']))->values();

        return view('backend.trainings.index', [
            'activeView' => $view,
            'trainings' => $trainings,
            'search' => $search,
            'statusOptions' => Training::statusOptions(),
            'selectedStatus' => $selectedStatus,
            'teamOptions' => Team::orderBy('name')->pluck('name', 'id'),
            'selectedTeam' => $selectedTeam,
            'calendarMonth' => $monthStart->format('Y-m'),
            'calendarMonthLabel' => ucfirst($monthStart->locale('es')->isoFormat('MMMM [de] YYYY')),
            'calendarTrainingsData' => $calendarTrainingsData,
            'isCoordinator' => in_array((int) auth()->user()?->role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER, User::ROLE_COORDINATOR]),
        ]);
    }

    private function formatDurationLabel(?int $durationMinutes): string
    {
        $minutes = (int) $durationMinutes;

        if ($minutes <= 0) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        return sprintf('%d h %02d min', $hours, $remainingMinutes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('backend.trainings.new', $this->buildFormViewData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $validated = $this->validateTraining($request);
        $scheduledAt = Carbon::parse($validated['training_date']);
        $selectedAttendance = $this->sanitizeAttendancePlayers(
            $validated['team'],
            $validated['attendance'] ?? []
        );

        DB::transaction(function () use ($validated, $request, $scheduledAt, $selectedAttendance) {
            $training = new Training([
                'name' => $validated['name'],
                'team' => $validated['team'],
                'venue' => $validated['venue'] ?? null,
                'location' => $validated['location'] ?? null,
                'duration' => $validated['duration'],
                'notes' => $validated['notes'] ?? null,
                'tactic_obj' => $validated['tactic_obj'] ?? null,
                'fisic_obj' => $validated['fisic_obj'] ?? null,
                'tecnic_obj' => $validated['tecnic_obj'] ?? null,
                'pyscho_obj' => $validated['pyscho_obj'] ?? null,
                'moment' => $validated['moment'] ?? null,
                'status' => $validated['status'],
            ]);

            $training->created_at = $scheduledAt;
            $training->save();

            $this->ensureTrainingStorage($training->id, $training->team);

            if ($request->hasFile('document')) {
                $documentPath = $this->storeTrainingDocument($training, $request->file('document'));
                if ($documentPath) {
                    $training->update(['document' => $documentPath]);
                }
            }
            $this->syncTrainingAttendance($training, $selectedAttendance, $scheduledAt);
        });

        return redirect()->route('trainings.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $modal = request('modal');

        if ($modal === 'attendance') {
            $training = $this->accessibleTrainingsQuery(['team.playerRosters.player', 'attendance.player.activeRoster'])->findOrFail($id);
            $activeRosters = $training->activeTeamRosters();
            $absentRosters = $training->absentPlayerRosters();

            return view('backend.trainings.attendance-modal', [
                'training' => $training,
                'activeRosters' => $activeRosters,
                'absentRosters' => $absentRosters,
            ]);
        }

        if ($modal === 'observations') {
            $training = $this->accessibleTrainingsQuery(['observations.author'])->findOrFail($id);
            $selectedObservationId = request()->input('observation');
            $selectedObservation = $selectedObservationId
                ? $training->observations->firstWhere('id', $selectedObservationId)
                : null;

            return view('backend.trainings.observations-modal', [
                'training' => $training,
                'trainingObservations' => $training->observations,
                'selectedObservation' => $selectedObservation,
                'isCoordinator' => in_array((int) auth()->user()?->role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER, User::ROLE_COORDINATOR]),
            ]);
        }

        $training = $this->accessibleTrainingsQuery([
            'team.managerRosters.user',
            'venue',
            'attendance.player.activeRoster',
            'observations.author',
        ])->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.trainings.show-modal', [
                'training' => $training,
            ]);
        }

        return redirect()->route('trainings.index');
    }

    public function viewDocument($id)
    {
        $training = $this->accessibleTrainingsQuery()->findOrFail($id);

        if (empty($training->document) || !Storage::disk('public')->exists($training->document)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($training->document));
    }

    public function downloadDocument($id)
    {
        $training = $this->accessibleTrainingsQuery()->findOrFail($id);

        if (empty($training->document) || !Storage::disk('public')->exists($training->document)) {
            abort(404);
        }

        return response()->download(Storage::disk('public')->path($training->document));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $training = $this->accessibleTrainingsQuery(['attendance', 'observations.author'])->findOrFail($id);
        $selectedObservationId = (string) request()->query('observation', '');
        $selectedObservation = $training->observations->firstWhere('id', $selectedObservationId);

        return view('backend.trainings.new', $this->buildFormViewData($training, $selectedObservation));
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
        $training = $this->accessibleTrainingsQuery(['attendance'])->findOrFail($id);
        $validated = $this->validateTraining($request);
        $scheduledAt = Carbon::parse($validated['training_date']);
        $selectedAttendance = $this->sanitizeAttendancePlayers(
            $validated['team'],
            $validated['attendance'] ?? []
        );

        DB::transaction(function () use ($validated, $request, $training, $scheduledAt, $selectedAttendance) {
            $previousTeamId = $training->team;

            $training->fill([
                'name' => $validated['name'],
                'team' => $validated['team'],
                'venue' => $validated['venue'] ?? null,
                'location' => $validated['location'] ?? null,
                'duration' => $validated['duration'],
                'notes' => $validated['notes'] ?? null,
                'tactic_obj' => $validated['tactic_obj'] ?? null,
                'fisic_obj' => $validated['fisic_obj'] ?? null,
                'tecnic_obj' => $validated['tecnic_obj'] ?? null,
                'pyscho_obj' => $validated['pyscho_obj'] ?? null,
                'moment' => $validated['moment'] ?? null,
                'status' => $validated['status'],
            ]);

            $training->created_at = $scheduledAt;
            $training->save();

            $this->syncTrainingStorage($training->id, $training->team, $previousTeamId);

            if ($request->input('remove_document') === '1') {
                $this->removeTrainingDocument($training);
            }

            if ($request->hasFile('document')) {
                $documentPath = $this->storeTrainingDocument($training, $request->file('document'));
                if ($documentPath) {
                    $training->update(['document' => $documentPath]);
                }
            }
            $this->syncTrainingAttendance($training, $selectedAttendance, $scheduledAt);
        });

        return redirect()->route('trainings.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $training = $this->accessibleTrainingsQuery()->findOrFail($id);
        $this->removeTrainingStorage($training->id, $training->team);
        $training->update(['status' => Training::INACTIVE]);

        return redirect()->route('trainings.index');
    }

    public function storeObservation(Request $request, string $id)
    {
        $training = $this->accessibleTrainingsQuery()->findOrFail($id);
        $validated = $request->validate([
            'note' => ['required', 'string'],
        ]);

        $training->observations()->create([
            'user_id' => Auth::id(),
            'note' => $validated['note'],
        ]);

        if ($request->input('_from') === 'modal') {
            return redirect()->route('trainings.index');
        }

        return redirect()->to(route('trainings.edit', $training->id) . '#training-observations');
    }

    public function updateObservation(Request $request, string $id, string $observationId)
    {
        $training = $this->accessibleTrainingsQuery(['observations'])->findOrFail($id);
        $observation = $training->observations()->findOrFail($observationId);
        $validated = $request->validate([
            'note' => ['required', 'string'],
        ]);

        $observation->update([
            'user_id' => Auth::id(),
            'note' => $validated['note'],
        ]);

        if ($request->input('_from') === 'modal') {
            return redirect()->route('trainings.index');
        }

        return redirect()->to(route('trainings.edit', $training->id) . '#training-observations');
    }

    /**
     * Remove the storage directory for a training. This method checks if the provided team ID is valid, and if so, 
     * it constructs the storage path for the training's folder based on the team and training IDs. 
     * It then attempts to delete the directory for the training using Laravel's Storage facade. 
     * If the storage path is not writable or if the deletion fails, it logs an error and flashes an error message to the session to inform the user of the issue. 
     * This method is called when a training is deleted to ensure that any associated files in storage are also removed.
     * @param string $trainingId The ID of the training for which to remove the storage directory. 
     * This should be a UUID string corresponding to the ID of the training being deleted.
     *
    */
    private function removeTrainingStorage(string $trainingId, ?string $teamId): void
    {
        if (empty($teamId)) {
            return;
        }

        $root = storage_path('app/public');

        if (!is_dir($root) || !is_writable($root)) {

            Log::error('Storage path is not writable for trainings.', [
                'path' => $root,
            ]);

            $this->flashStorageError();

            return;
        }

        $disk = Storage::disk('public');
        $path = TenantStorage::path("teams/{$teamId}/trainings/{$trainingId}");

        if ($disk->exists($path) && !$disk->deleteDirectory($path)) {

            Log::error('Failed to delete training folder.', [
                'training' => $trainingId,
                'team' => $teamId,
                'path' => $path,
            ]);

            $this->flashStorageError();
        }
    }

    private function validateTraining(Request $request): array
    {
        return $request->validate([
            'training_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:250'],
            'team' => ['required', 'uuid', 'exists:teams,id'],
            'venue' => ['nullable', 'uuid', 'exists:sports_venues,id'],
            'location' => ['nullable', 'string', 'max:250'],
            'duration' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'tactic_obj' => ['nullable', 'integer', 'min:0'],
            'fisic_obj' => ['nullable', 'integer', 'min:0'],
            'tecnic_obj' => ['nullable', 'integer', 'min:0'],
            'pyscho_obj' => ['nullable', 'integer', 'min:0'],
            'moment' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'integer', 'in:' . implode(',', array_keys(Training::statusOptions()))],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            'remove_document' => ['nullable', 'in:0,1'],
            'attendance' => ['nullable', 'array'],
            'attendance.*' => ['uuid', 'exists:players,id'],
        ]);
    }

    private function buildFormViewData(?Training $training = null, ?TrainingObservation $selectedObservation = null): array
    {
        return [
            'isEdit' => $training !== null,
            'training' => $training,
            'teams' => Team::orderBy('name')->get(),
            'venues' => SportsVenue::orderBy('name')->get(),
            'statusOptions' => Training::statusOptions(),
            'playersByTeam' => $this->getPlayersByTeam(),
            'selectedAttendance' => $training?->attendance?->pluck('player')->values()->all() ?? [],
            'trainingObservations' => $training?->observations ?? collect(),
            'selectedObservation' => $selectedObservation,
            'isCoordinator' => in_array((int) auth()->user()?->role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER, User::ROLE_COORDINATOR]),
        ];
    }

    private function accessibleTrainingsQuery(array $with = [])
    {
        $query = Training::query()->with($with);
        $scopedTeamIds = auth()->user()?->scopedTeamIds();

        if ($scopedTeamIds !== null) {
            $query->whereIn('team', $scopedTeamIds);
        }

        return $query;
    }

    private function getPlayersByTeam(): array
    {
        $positions = Player::positionOptions();

        return PlayerRoster::with('player')
            ->where('status', PlayerRoster::ACTIVE)
            ->get()
            ->filter(function (PlayerRoster $roster) {
                $player = $roster->relationLoaded('player') ? $roster->getRelation('player') : null;

                return $player && (int) $player->status === Player::ACTIVE;
            })
            ->sortBy([
                ['team', 'asc'],
                [fn (PlayerRoster $roster) => $roster->dorsal ?? 999, 'asc'],
                [function (PlayerRoster $roster) {
                    $player = $roster->relationLoaded('player') ? $roster->getRelation('player') : null;

                    return strtolower(trim(($player?->name ?? '') . ' ' . ($player?->lastname ?? '')));
                }, 'asc'],
            ])
            ->groupBy('team')
            ->map(function ($rosters) use ($positions) {
                return $rosters->map(function (PlayerRoster $roster) use ($positions) {
                    $player = $roster->relationLoaded('player') ? $roster->getRelation('player') : null;

                    return [
                        'id' => $player?->id,
                        'name' => trim(($player?->name ?? '') . ' ' . ($player?->lastname ?? '')),
                        'dorsal' => $roster->dorsal,
                        'position' => $positions[$roster->position] ?? ($positions[$player?->primary_position] ?? null),
                    ];
                })->filter(fn (array $player) => !empty($player['id']))->values()->all();
            })
            ->all();
    }

    private function sanitizeAttendancePlayers(string $teamId, array $attendance): array
    {
        $allowedPlayerIds = PlayerRoster::where('team', $teamId)
            ->where('status', PlayerRoster::ACTIVE)
            ->pluck('player')
            ->map(fn ($playerId) => (string) $playerId)
            ->all();

        $selectedAttendance = collect($attendance)
            ->map(fn ($playerId) => (string) $playerId)
            ->unique()
            ->values()
            ->all();

        $invalidPlayers = array_diff($selectedAttendance, $allowedPlayerIds);

        if (!empty($invalidPlayers)) {
            throw ValidationException::withMessages([
                'attendance' => 'Solo puedes seleccionar jugadores que pertenezcan al equipo del entrenamiento.',
            ]);
        }

        return $selectedAttendance;
    }

    private function syncTrainingAttendance(Training $training, array $selectedAttendance, Carbon $attendanceTime): void
    {
        $training->attendance()->delete();

        foreach ($selectedAttendance as $playerId) {
            TrainingAttendance::create([
                'training' => $training->id,
                'player' => $playerId,
                'created_at' => $attendanceTime->copy(),
            ]);
        }
    }

    private function ensureTrainingStorage(string $trainingId, ?string $teamId): void
    {
        if (empty($teamId) || !$this->ensureStorageWritable()) {
            return;
        }

        $disk = Storage::disk('public');
        $basePath = TenantStorage::path("teams/{$teamId}/trainings/{$trainingId}");

        if (!$disk->exists($basePath) && !$disk->makeDirectory($basePath)) {
            Log::error('Failed to create training folder.', [
                'training' => $trainingId,
                'team' => $teamId,
                'path' => $basePath,
            ]);

            $this->flashStorageError();
            return;
        }

        foreach (['reports', 'photos'] as $folder) {
            $folderPath = "{$basePath}/{$folder}";

            if (!$disk->exists($folderPath) && !$disk->makeDirectory($folderPath)) {
                Log::error('Failed to create training subfolder.', [
                    'training' => $trainingId,
                    'team' => $teamId,
                    'path' => $folderPath,
                ]);

                $this->flashStorageError();
            }
        }
    }

    private function syncTrainingStorage(string $trainingId, ?string $teamId, ?string $previousTeamId): void
    {
        if (empty($teamId)) {
            return;
        }

        if (!$this->ensureStorageWritable()) {
            return;
        }

        $disk = Storage::disk('public');
        $newPath = TenantStorage::path("teams/{$teamId}/trainings/{$trainingId}");

        if (!empty($previousTeamId) && $previousTeamId !== $teamId) {
            $previousPath = TenantStorage::path("teams/{$previousTeamId}/trainings/{$trainingId}");

            if ($disk->exists($previousPath)) {
                if ($disk->exists($newPath)) {
                    $disk->deleteDirectory($newPath);
                }

                if (!$disk->move($previousPath, $newPath)) {
                    Log::error('Failed to move training folder.', [
                        'training' => $trainingId,
                        'from_team' => $previousTeamId,
                        'to_team' => $teamId,
                    ]);

                    $this->flashStorageError();
                }
            }
        }

        $this->ensureTrainingStorage($trainingId, $teamId);
    }

    private function storeTrainingDocument(Training $training, UploadedFile $document): ?string
    {
        if (empty($training->team) || !$this->ensureStorageWritable()) {
            return null;
        }

        $disk = Storage::disk('public');
        $basePath = TenantStorage::path("teams/{$training->team}/trainings/{$training->id}/reports");
        $extension = strtolower($document->getClientOriginalExtension());
        $suffix = $extension ? ".{$extension}" : '';
        $filename = 'documento-' . now()->format('YmdHis') . $suffix;

        if (!empty($training->document) && $disk->exists($training->document)) {
            $disk->delete($training->document);
        }

        $storedPath = $disk->putFileAs($basePath, $document, $filename);

        return $storedPath ?: null;
    }

    private function removeTrainingDocument(Training $training): void
    {
        if (empty($training->document)) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($training->document)) {
            $disk->delete($training->document);
        }

        $training->update(['document' => null]);
    }

    private function ensureStorageWritable(): bool
    {
        $root = storage_path('app/public');

        if (is_dir($root) && is_writable($root)) {
            return true;
        }

        Log::error('Storage path is not writable for trainings.', [
            'path' => $root,
        ]);

        $this->flashStorageError();

        return false;
    }

    /**
     * Flash an error message to the session indicating that there was an issue with storage permissions. 
     * This method ensures that the error message is only flashed once per request to avoid duplicate messages in the session. 
     * It is called whenever there is a failure to delete necessary directories in storage, such as when removing folders for trainings, 
     * to inform the user that some folders could not be deleted due to permission issues.
     * @return void
    */
    private function flashStorageError(): void
    {
        if ($this->storageErrorShown) {
            return;
        }

        $this->storageErrorShown = true;
        session()->flash('error', 'No se pudieron borrar carpetas de entrenamientos. Revisa permisos de storage.');
    }
}
