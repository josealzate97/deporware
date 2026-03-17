<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ManagerRoster;
use App\Models\Player;
use App\Models\PlayerRoster;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeamsController extends Controller
{   
    private bool $storageErrorShown = false;
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

        $search = trim((string) request()->query('search', ''));
        $status = (string) request()->query('status', '');
        $seasonFilter = trim((string) request()->query('season', ''));
        $yearFilter = trim((string) request()->query('year', ''));
        $statusOptions = [
            '1' => 'Activas',
            '0' => 'Inactivas',
        ];

        if ($status !== '' && !array_key_exists($status, $statusOptions)) {
            $status = '';
        }

        $teamsQuery = Team::query()
            ->with(['managerRosters.user'])
            ->withCount(['playerRosters as players_count' => function ($query) {
                $query->where('status', 1);
            }])
            ->where('type', $typeOptions[$activeType])
            ->orderByDesc('status')
            ->orderBy('name');

        if ($seasonFilter !== '') {
            $teamsQuery->where('season', 'like', '%' . $seasonFilter . '%');
        }

        if ($yearFilter !== '') {
            $teamsQuery->where('year', $yearFilter);
        }

        if ($search !== '') {
            $teamsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('season', 'like', '%' . $search . '%')
                    ->orWhere('year', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '') {
            $teamsQuery->where('status', (int) $status);
        }

        $teams = $teamsQuery->paginate(10)->withQueryString();

        return view('backend.teams.index', [
            'teams' => $teams,
            'activeType' => $activeType,
            'statusOptions' => $statusOptions,
            'search' => $search,
            'selectedStatus' => $status,
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
            'players' => $this->getAvailablePlayers(),
            'selectedPlayerIds' => [],
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
            'year' => 'required|string|max:9',
            'type' => 'required|integer|in:1,2',
            'season' => 'required|string|max:20',
            'status' => 'nullable|boolean',
            'venues' => 'nullable|array',
            'venues.*' => 'uuid|exists:sports_venues,id',
            'players' => 'nullable|array',
            'players.*' => 'uuid|exists:players,id',
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

        $playerIds = $data['players'] ?? [];
        unset($data['players']);

        $this->ensurePlayersAreAssignable($playerIds);

        $team = Team::create($data);
        $this->ensureTeamStorage($team->id);

        if (!empty($venueIds)) {
            $team->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        }

        $this->syncTeamCoaches($team, $coachPrimaryId, $coachSecondaryId);
        $this->syncTeamPlayers($team, $playerIds);

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
        $team = Team::with([
            'venues',
            'managerRosters.user',
            'playerRosters.player',
            'matches' => function ($query) {
                $query->with('rival')
                    ->orderByDesc('match_date')
                    ->limit(10);
            },
            'trainings' => function ($query) {
                $query->orderByDesc('created_at')
                    ->limit(10);
            },
        ])->findOrFail($id);
        
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
        $team = Team::with(['venues', 'managerRosters', 'playerRosters'])->findOrFail($id);

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
            'players' => $this->getAvailablePlayers($team->id),
            'selectedPlayerIds' => $team->playerRosters->where('status', 1)->pluck('player')->all(),
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
            'year' => 'required|string|max:9',
            'type' => 'required|integer|in:1,2',
            'season' => 'required|string|max:20',
            'status' => 'nullable|boolean',
            'venues' => 'nullable|array',
            'venues.*' => 'uuid|exists:sports_venues,id',
            'players' => 'nullable|array',
            'players.*' => 'uuid|exists:players,id',
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

        $playerIds = $data['players'] ?? [];
        unset($data['players']);

        $team = Team::findOrFail($id);
        $this->ensurePlayersAreAssignable($playerIds, $team->id);
        $team->update($data);

        if (!empty($venueIds)) {
            $team->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        } else {
            $team->venues()->detach();
        }

        $this->syncTeamCoaches($team, $coachPrimaryId, $coachSecondaryId);
        $this->syncTeamPlayers($team, $playerIds);

        $redirectType = ((int) $data['type'] === Team::TYPE_FORMATIVE) ? 'formative' : 'competitive';

        return redirect()->route('teams.index', ['type' => $redirectType]);
    }

    /**
     * Get players available to be assigned in the team form.
     */
    private function getAvailablePlayers(?string $currentTeamId = null)
    {
        $occupiedPlayersQuery = PlayerRoster::query()
            ->where('status', PlayerRoster::ACTIVE);

        if ($currentTeamId) {
            $occupiedPlayersQuery->where('team', '!=', $currentTeamId);
        }

        $occupiedPlayerIds = $occupiedPlayersQuery
            ->select('player')
            ->distinct()
            ->pluck('player');

        return Player::query()
            ->where('status', Player::ACTIVE)
            ->whereNotIn('id', $occupiedPlayerIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Prevent assigning players that already have an active roster in another team.
     */
    private function ensurePlayersAreAssignable(array $playerIds, ?string $currentTeamId = null): void
    {
        if (empty($playerIds)) {
            return;
        }

        $conflicts = PlayerRoster::query()
            ->where('status', PlayerRoster::ACTIVE)
            ->whereIn('player', $playerIds)
            ->when($currentTeamId, fn ($query) => $query->where('team', '!=', $currentTeamId))
            ->with('player:id,name,lastname')
            ->get();

        if ($conflicts->isEmpty()) {
            return;
        }

        $names = $conflicts
            ->map(fn (PlayerRoster $roster) => trim((string) optional($roster->player)->name . ' ' . (string) optional($roster->player)->lastname))
            ->filter()
            ->unique()
            ->values()
            ->take(3)
            ->implode(', ');

        $suffix = $conflicts->count() > 3 ? ' y otros.' : '.';

        throw ValidationException::withMessages([
            'players' => 'Uno o más jugadores ya están asociados a otra plantilla activa: ' . $names . $suffix,
        ]);
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

    /**
     * Sync the coaches for a team based on the provided primary and secondary coach IDs. This method will first remove any existing coach associations for the team,
     * and then create new associations based on the provided IDs. If a primary coach ID is provided, it will create an association with the role of primary coach.
     * If a secondary coach ID is provided, it will create an association with the role of assistant coach.
     * This method ensures that the team's coaching roster is updated to reflect the current selections made in the team management interface.
     * @param Team $team The team for which to sync the coaches. This should be an instance of the Team model that has already been created or updated.
     * @param string|null $coachPrimaryId The ID of the primary coach to associate with the team. If null, no primary coach will be associated.
     * @param string|null $coachSecondaryId The ID of the secondary coach to associate with the team. If null, no secondary coach will be associated.
     * @return void 
    */
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

    /**
     * Sync the players for a team based on the provided player IDs. 
     * This method will first update any existing player roster entries for the team to inactive if their player ID is not in the provided list of player IDs.
     * Then, it will create or update roster entries for each player ID in the provided list, setting their status to active and updating their position
     * and dorsal information based on the current data in the players table. Additionally, this method will ensure that storage directories exist for each
     * player under the team's folder in storage, creating them if necessary. This ensures that the team's player roster is accurately reflected in both the
     * database and the file storage structure.
     * @param Team $team The team for which to sync the players. This should be an instance of the Team model that has already been created or updated.
     * @param array $playerIds An array of player IDs that should be associated with the team. 
     * This should be an array of UUID strings corresponding to the IDs of players in the players table. 
     * Any player ID in this list that is not currently associated with the team will be added, 
     * and any player currently associated with the team that is not in this list will be marked as inactive in the roster.
     * @return void
    */
    private function syncTeamPlayers(Team $team, array $playerIds): void
    {
        $playerIds = array_values(array_unique(array_filter($playerIds)));

        if (empty($playerIds)) {
            PlayerRoster::where('team', $team->id)->update(['status' => 0]);
            return;
        }

        PlayerRoster::where('team', $team->id)
        ->whereNotIn('player', $playerIds)
        ->update(['status' => 0]);

        $players = Player::whereIn('id', $playerIds)->get();

        foreach ($players as $player) {
            $previousTeamId = PlayerRoster::where('player', $player->id)
                ->where('status', 1)
                ->where('team', '!=', $team->id)
                ->value('team');

            PlayerRoster::where('player', $player->id)
                ->where('team', '!=', $team->id)
                ->update(['status' => 0]);

            PlayerRoster::updateOrCreate(
                ['team' => $team->id, 'player' => $player->id],
                [
                    'position' => $player->primary_position ?? $player->position,
                    'dorsal' => $player->dorsal,
                    'status' => 1,
                ]
            );

            $this->syncPlayerFolderAcrossTeams($player->id, $team->id, $previousTeamId);
            
        }
    }

    private function syncPlayerFolderAcrossTeams(string $playerId, string $teamId, ?string $previousTeamId): void
    {
        $this->ensureStorageWritable();

        $disk = Storage::disk('public');
        $newPath = "teams/{$teamId}/players/{$playerId}";

        if (!empty($previousTeamId) && $previousTeamId !== $teamId) {
            $oldPath = "teams/{$previousTeamId}/players/{$playerId}";

            if ($disk->exists($oldPath)) {
                if ($disk->exists($newPath)) {
                    $disk->deleteDirectory($newPath);
                }

                if (!$this->copyDirectory($disk, $oldPath, $newPath)) {
                    Log::error('Failed to copy player folder to reassigned team.', [
                        'player' => $playerId,
                        'from' => $oldPath,
                        'to' => $newPath,
                    ]);
                    $this->flashStorageError();
                    return;
                }

                $this->syncPlayerPhotoPath($playerId, $previousTeamId, $teamId);
                return;
            }
        }

        $this->ensureTeamPlayerFolder($teamId, $playerId);
    }

    private function copyDirectory($disk, string $source, string $destination): bool
    {
        if (!$disk->exists($source)) {
            return false;
        }

        if (!$disk->exists($destination) && !$disk->makeDirectory($destination)) {
            return false;
        }

        foreach ($disk->allDirectories($source) as $directory) {
            $relative = Str::after($directory, $source . '/');
            $targetDirectory = $destination . ($relative !== $directory ? '/' . $relative : '');

            if (!$disk->exists($targetDirectory) && !$disk->makeDirectory($targetDirectory)) {
                return false;
            }
        }

        foreach ($disk->allFiles($source) as $file) {
            $relative = Str::after($file, $source . '/');
            $targetFile = $destination . '/' . $relative;

            if (!$disk->copy($file, $targetFile)) {
                return false;
            }
        }

        return true;
    }

    private function syncPlayerPhotoPath(string $playerId, string $oldTeamId, string $newTeamId): void
    {
        $player = Player::find($playerId);
        if (!$player || empty($player->photo)) {
            return;
        }

        $oldPrefix = "teams/{$oldTeamId}/players/{$playerId}/";
        $newPrefix = "teams/{$newTeamId}/players/{$playerId}/";

        if (Str::startsWith($player->photo, $oldPrefix)) {
            $player->update([
                'photo' => Str::replaceFirst($oldPrefix, $newPrefix, $player->photo),
            ]);
        }
    }

    /**
     * Ensure that the storage directories for a team exist. This method checks if the storage path is writable and logs an error if it is not. 
     * It then attempts to create the necessary directories for the team, including folders for players, matches, and trainings. 
     * If any of the required directories cannot be created, it logs an error for each failure. This method is used when creating or updating a team 
     * to ensure that the necessary storage structure is in place for storing files related to the team, its players, matches, and trainings.
     * @param string $teamId The ID of the team for which to ensure storage directories. This should be a UUID string corresponding to the ID 
     * of a team in the teams table.
     * @return void
    */
    private function ensureTeamStorage(string $teamId): void
    {
        $this->ensureStorageWritable();
        $disk = Storage::disk('public');

        foreach (["teams/{$teamId}/players", "teams/{$teamId}/matches", "teams/{$teamId}/trainings"] as $path) {

            if (!$disk->exists($path) && !$disk->makeDirectory($path)) {

                Log::error('Failed to create team folder.', [
                    'team' => $teamId,
                    'path' => $path,
                ]);

                $this->flashStorageError();
            }
        }

        // Match/Training subfolders are created per record (by UUID) when they are created.
    }

    /**
     * Ensure that the storage directories for a player within a team exist. This method checks if the storage path is writable and logs an error if it is not.
     * It then attempts to create the necessary directories for the player under the team's folder in storage, 
     * including subfolders for photos and documents. If any of the required directories cannot be created, it
     * logs an error for each failure. This method is used when associating a player with a team to ensure that the necessary 
     * storage structure is in place for storing files related to the player within the context of the team.
     * @param string $teamId The ID of the team to which the player is being associated. This should be a UUID string corresponding to the ID of a team in the teams table.
     * @param string $playerId The ID of the player for which to ensure storage directories. This should be a UUID string corresponding to the ID of a player in the players table.
     * @return void
    */
    private function ensureTeamPlayerFolder(string $teamId, string $playerId): void
    {
        $this->ensureStorageWritable();

        $disk = Storage::disk('public');
        $playerPath = "teams/{$teamId}/players/{$playerId}";

        if (!$disk->makeDirectory($playerPath)) {

            Log::error('Failed to create player folder for team.', [
                'team' => $teamId,
                'player' => $playerId,
                'path' => $playerPath,
            ]);

        }

        $this->ensurePlayerSubfolders($disk, $playerPath);
    }

    /**
     * Ensure that the storage path is writable for creating team and player folders. This method checks if the storage directory exists and is writable,
     * and logs an error if it is not. If the storage path is not writable, it also flashes an error message to the session to inform the user that some folders
     * could not be created due to permission issues. This method is called before attempting to create any directories in storage to ensure that the application can properly 
     * handle cases where the storage configuration may not allow for directory creation.
     * @return void
    */
    private function ensureStorageWritable(): void
    {
        $root = storage_path('app/public');
        if (!is_dir($root) || !is_writable($root)) {
            Log::error('Storage path is not writable for team/player folders.', [
                'path' => $root,
            ]);
            $this->flashStorageError();
        }
    }

    /**
     * Ensure that the necessary subfolders for a player exist in storage. This method checks for the existence of the 'photos' and 'documents' subfolders
     * under the player's folder in storage, and attempts to create them if they do not exist. 
     * If any of the required subfolders cannot be created, it logs an error for each failure.
     * This method is used to maintain the expected storage structure for players within a team, 
     * ensuring that there are designated folders for storing player photos and documents.
     * @param string $playerPath The storage path for the player's folder, which should be in the format "teams/{teamId}/players/{playerId}". 
     * This path is used to determine where to create the 'photos' and 'documents' subfolders for the player.
     * @return void
    */
    private function ensurePlayerSubfolders($disk, string $playerPath): void
    {
        foreach (['photos', 'documents'] as $folder) {
            $fullPath = "{$playerPath}/{$folder}";
            if (!$disk->exists($fullPath) && !$disk->makeDirectory($fullPath)) {
                Log::error('Failed to create player subfolder.', [
                    'path' => $fullPath,
                ]);
                $this->flashStorageError();
            }
        }
    }

    /**
     * Flash an error message to the session indicating that there was an issue with storage permissions. 
     * This method ensures that the error message is only flashed once per request to avoid duplicate messages in the session. 
     * It is called whenever there is a failure to create necessary directories in storage, such as when setting up folders for teams or players, 
     * to inform the user that some folders could not be created due to permission issues.
     * @return void
    */
    private function flashStorageError(): void
    {
        if ($this->storageErrorShown) {
            return;
        }

        $this->storageErrorShown = true;
        session()->flash('error', 'No se pudieron crear algunas carpetas en storage. Revisa permisos de escritura.');
    }
}
