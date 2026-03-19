<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerRoster;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\Training;
use App\Models\TrainingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TrainingsController extends Controller
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

        $trainingsQuery = Training::with('team')
            ->orderByDesc('status')
            ->orderBy('name');

        $trainings = (clone $trainingsQuery)->get();

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
                'duration' => $training->duration ? $training->duration . ' min' : '-',
            ];
        })->filter(fn ($item) => !empty($item['date']))->values();

        return view('backend.trainings.index', [
            'activeView' => $view,
            'trainings' => $trainings,
            'calendarMonth' => $monthStart->format('Y-m'),
            'calendarMonthLabel' => ucfirst($monthStart->locale('es')->isoFormat('MMMM [de] YYYY')),
            'calendarTrainingsData' => $calendarTrainingsData,
        ]);
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
                'principal_obj' => $validated['principal_obj'] ?? null,
                'tactic_obj' => $validated['tactic_obj'] ?? null,
                'fisic_obj' => $validated['fisic_obj'] ?? null,
                'tecnic_obj' => $validated['tecnic_obj'] ?? null,
                'pyscho_obj' => $validated['pyscho_obj'] ?? null,
                'moment' => $validated['moment'] ?? null,
                'status' => $validated['status'],
            ]);

            if ($request->hasFile('document')) {
                $training->document = file_get_contents($request->file('document')->getRealPath());
            }

            $training->created_at = $scheduledAt;
            $training->save();

            $this->ensureTrainingStorage($training->id, $training->team);
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
        $training = Training::with('team')->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.trainings.show-modal', [
                'training' => $training,
            ]);
        }

        return redirect()->route('trainings.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $training = Training::with('attendance')->findOrFail($id);

        return view('backend.trainings.new', $this->buildFormViewData($training));
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
        $training = Training::with('attendance')->findOrFail($id);
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
                'principal_obj' => $validated['principal_obj'] ?? null,
                'tactic_obj' => $validated['tactic_obj'] ?? null,
                'fisic_obj' => $validated['fisic_obj'] ?? null,
                'tecnic_obj' => $validated['tecnic_obj'] ?? null,
                'pyscho_obj' => $validated['pyscho_obj'] ?? null,
                'moment' => $validated['moment'] ?? null,
                'status' => $validated['status'],
            ]);

            if ($request->hasFile('document')) {
                $training->document = file_get_contents($request->file('document')->getRealPath());
            }

            $training->created_at = $scheduledAt;
            $training->save();

            $this->syncTrainingStorage($training->id, $training->team, $previousTeamId);
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
        $training = Training::findOrFail($id);
        $this->removeTrainingStorage($training->id, $training->team);
        $training->update(['status' => Training::INACTIVE]);

        return redirect()->route('trainings.index');
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
        $path = "teams/{$teamId}/trainings/{$trainingId}";

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
            'principal_obj' => ['nullable', 'integer', 'min:0'],
            'tactic_obj' => ['nullable', 'integer', 'min:0'],
            'fisic_obj' => ['nullable', 'integer', 'min:0'],
            'tecnic_obj' => ['nullable', 'integer', 'min:0'],
            'pyscho_obj' => ['nullable', 'integer', 'min:0'],
            'moment' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'integer', 'in:' . implode(',', array_keys(Training::statusOptions()))],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx'],
            'attendance' => ['nullable', 'array'],
            'attendance.*' => ['uuid', 'exists:players,id'],
        ]);
    }

    private function buildFormViewData(?Training $training = null): array
    {
        return [
            'isEdit' => $training !== null,
            'training' => $training,
            'teams' => Team::orderBy('name')->get(),
            'venues' => SportsVenue::orderBy('name')->get(),
            'statusOptions' => Training::statusOptions(),
            'playersByTeam' => $this->getPlayersByTeam(),
            'selectedAttendance' => $training?->attendance?->pluck('player')->values()->all() ?? [],
        ];
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
        $basePath = "teams/{$teamId}/trainings/{$trainingId}";

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
        $newPath = "teams/{$teamId}/trainings/{$trainingId}";

        if (!empty($previousTeamId) && $previousTeamId !== $teamId) {
            $previousPath = "teams/{$previousTeamId}/trainings/{$trainingId}";

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
