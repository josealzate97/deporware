<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Support\TenantStorage;
use App\Models\PlayerObservation;
use App\Models\PlayerRoster;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlayersController extends Controller
{   
    private bool $storageErrorShown = false;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $position = (string) $request->query('position', '');
        $team = (string) $request->query('team', '');

        $positionOptions = Player::positionOptions();
        if ($position !== '' && !array_key_exists((int) $position, $positionOptions)) {
            $position = '';
        }

        // Scoping por rol: coordinator y coach solo ven sus equipos
        $scopedTeamIds = auth()->user()->scopedTeamIds();

        $teamOptions = Team::query()
            ->when($scopedTeamIds !== null, fn($q) => $q->whereIn('id', $scopedTeamIds))
            ->orderBy('name')
            ->pluck('name', 'id');

        $playersQuery = Player::with([
            'rosters' => function ($query) {
                $query->with('team')
                    ->orderByDesc('status')
                    ->orderByDesc('created_at');
            },
        ]);

        // Limitar jugadores a los que pertenecen a los equipos del scope
        if ($scopedTeamIds !== null) {
            $playersQuery->whereHas('rosters', function ($rosterQuery) use ($scopedTeamIds) {
                $rosterQuery->whereIn('team', $scopedTeamIds);
            });
        }

        if ($search !== '') {
            $playersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('rosters.team', function ($teamQuery) use ($search) {
                        $teamQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($position !== '') {
            $playersQuery->where(function ($query) use ($position) {
                $query->whereJsonContains('positions', (int) $position)
                    ->orWhere('position', (int) $position);
            });
        }

        if ($team !== '') {
            $playersQuery->whereHas('rosters', function ($rosterQuery) use ($team) {
                $rosterQuery->where('team', $team);
            });
        }

        $players = $playersQuery
            ->orderByDesc('status')
            ->orderBy('name')
            ->orderBy('lastname')
            ->paginate(10)
            ->withQueryString();

        return view('backend.players.index', [
            'players' => $players,
            'positionOptions' => $positionOptions,
            'teamOptions' => $teamOptions,
            'observationTypes' => PlayerObservation::typeOptions(),
            'search' => $search,
            'selectedPosition' => $position,
            'selectedTeam' => $team,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $scopedTeamIds = auth()->user()->scopedTeamIds();

        $teamOptions = Team::query()
            ->where('status', Team::ACTIVE)
            ->when($scopedTeamIds !== null, fn($q) => $q->whereIn('id', $scopedTeamIds))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('backend.players.new', [
            'isEdit' => false,
            'player' => new Player(),
            'nationalityOptions' => Player::nationalityOptions(),
            'positionOptions' => Player::positionOptions(),
            'footOptions' => Player::footOptions(),
            'teamOptions' => $teamOptions,
            'selectedTeamId' => null,
            'observationTypes' => PlayerObservation::typeOptions(),
            'step' => 'player',
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
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'nit' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
            'nacionality' => ['required', 'integer', Rule::in(array_keys(Player::nationalityOptions()))],
            'positions' => ['required', 'array', 'min:1'],
            'positions.*' => ['integer', 'distinct', Rule::in(array_keys(Player::positionOptions()))],
            'dorsal' => 'nullable|integer|min:0',
            'foot' => ['required', 'integer', Rule::in(array_keys(Player::footOptions()))],
            'weight' => 'required|integer|min:0',
            'status' => 'nullable|boolean',
            'team_id' => ['nullable', 'uuid', Rule::exists('teams', 'id'), 'required_with:photo'],
            'remove_photo' => ['nullable', 'boolean'],
            'initial_observation_type' => ['nullable', 'integer', Rule::in(array_keys(PlayerObservation::typeOptions()))],
            'initial_observation_notes' => 'nullable|string',
        ]);

        $validated['status'] = $request->boolean('status') ? Player::ACTIVE : Player::INACTIVE;
        $validated['positions'] = Player::normalizePositions($validated['positions'] ?? []);
        $validated['position'] = $validated['positions'][0] ?? null;

        $initialObservationType = $validated['initial_observation_type'] ?? null;
        $initialObservationNotes = $validated['initial_observation_notes'] ?? null;
        $teamId = $validated['team_id'] ?? null;
        unset($validated['initial_observation_type'], $validated['initial_observation_notes']);
        unset($validated['team_id']);

        $player = Player::create($validated);

        if (!empty($initialObservationType)) {
            $player->observations()->create([
                'type' => $initialObservationType,
                'notes' => $initialObservationNotes ?? null,
                'user' => Auth::id(),
                'status' => PlayerObservation::ACTIVE,
            ]);
        }

        $this->syncPlayerTeamRoster($player, $teamId);
        $this->storePlayerPhoto($player, $teamId, $request->file('photo'));

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $player = Player::with(['contacts', 'observations.author', 'rosters.team'])->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.players.show-modal', [
                'player' => $player,
                'observationTypes' => PlayerObservation::typeOptions(),
                'playerDocuments' => $this->getPlayerDocuments($player),
            ]);
        }

        return redirect()->route('players.index');
    }

    /**
     * Download a player document file from allowed team/player folders.
     */
    public function downloadDocument(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $encoded = (string) $request->query('file', '');

        $path = base64_decode($encoded, true);
        if ($path === false || $path === '') {
            abort(404);
        }

        $teamIds = $this->getPlayerTeamIds($player->id);
        if (empty($teamIds)) {
            abort(404);
        }

        $allowed = collect($teamIds)->contains(function ($teamId) use ($path, $player) {
            return Str::startsWith($path, TenantStorage::path("teams/{$teamId}/players/{$player->id}/documents/"))
                || Str::startsWith($path, TenantStorage::path("teams/{$teamId}/players/{$player->id}/reports/"));
        });

        if (!$allowed) {
            abort(403);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404);
        }

        return response()->download(storage_path('app/public/' . $path), basename($path));
    }

    public function downloadScoutingReport($id)
    {
        $player = Player::with([
            'observations' => function ($query) {
                $query
                    ->where('status', PlayerObservation::ACTIVE)
                    ->latest('created_at');
            },
            'observations.author',
        ])->findOrFail($id);

        $defensivePositions = [
            Player::POSICION_ARQUERO,
            Player::POSICION_DEFENSA_CENTRAL,
            Player::POSICION_LATERAL_DERECHO,
            Player::POSICION_LATERAL_IZQUIERDO,
            Player::POSICION_MEDIOCAMPISTA_DEFENSIVO,
        ];
        $primaryPositionValue = $player->primary_position ?? $player->position;
        $mentalidad = in_array($primaryPositionValue, $defensivePositions, true) ? 'defensiva' : 'ofensiva';

        $pdf = Pdf::loadView('backend.templates.scouting-report', [
            'player'    => $player,
            'mentalidad' => $mentalidad,
        ])->setPaper('a4', 'portrait');

        $filename = Str::slug(trim(($player->name ?? '') . ' ' . ($player->lastname ?? ''))) . '-ficha-valorativa.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $player = Player::with(['contacts', 'observations.author', 'rosters'])->findOrFail($id);

        $teamOptions = Team::query()
            ->where('status', Team::ACTIVE)
            ->orderBy('name')
            ->pluck('name', 'id');

        $selectedTeamId = $player->rosters
            ->sortByDesc('created_at')
            ->firstWhere('status', PlayerRoster::ACTIVE)?->team
            ?? $player->rosters->sortByDesc('created_at')->first()?->team;

        return view('backend.players.new', [
            'isEdit' => true,
            'player' => $player,
            'nationalityOptions' => Player::nationalityOptions(),
            'positionOptions' => Player::positionOptions(),
            'footOptions' => Player::footOptions(),
            'teamOptions' => $teamOptions,
            'selectedTeamId' => $selectedTeamId,
            'observationTypes' => PlayerObservation::typeOptions(),
            'playerDocuments' => $this->getPlayerDocuments($player),
            'step' => request()->query('step', 'player'),
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
        $player = Player::findOrFail($id);
        $step = $request->input('step', 'player');

        if ($step === 'contacts') {
            $relationshipOptions = \App\Models\PlayerContact::relationshipOptions();
            $validated = $request->validate([
                'contact_id' => 'nullable|uuid',
                'contact_name' => 'required|string|max:100',
                'contact_lastname' => 'required|string|max:100',
                'contact_relationship' => ['required', 'integer', Rule::in(array_keys($relationshipOptions))],
                'contact_email' => 'required|email|max:100',
                'contact_phone' => 'required|string|max:20',
                'contact_address' => 'required|string|max:80',
                'contact_city' => 'required|string|max:80',
                'contact_status' => 'nullable|boolean',
            ]);

            $contactPayload = [
                'name' => $validated['contact_name'],
                'lastname' => $validated['contact_lastname'],
                'relationship' => $validated['contact_relationship'],
                'email' => $validated['contact_email'],
                'phone' => $validated['contact_phone'],
                'address' => $validated['contact_address'],
                'city' => $validated['contact_city'],
                'status' => $request->boolean('contact_status') ? 1 : 0,
            ];

            $contact = null;
            if (!empty($validated['contact_id'])) {
                $contact = $player->contacts()->where('id', $validated['contact_id'])->first();
            }

            if ($contact) {
                $contact->update($contactPayload);
            } else {
                $player->contacts()->create($contactPayload);
            }

            return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
        }

        if ($step === 'observations') {
            $validated = $request->validate([
                'observation_id' => 'nullable|uuid',
                'type' => ['required', 'integer', Rule::in(array_keys(PlayerObservation::typeOptions()))],
                'notes' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]);

            $payload = [
                'type' => $validated['type'],
                'notes' => $validated['notes'] ?? null,
                'status' => $request->boolean('status') ? PlayerObservation::ACTIVE : PlayerObservation::INACTIVE,
                'user' => Auth::id(),
            ];

            $observation = null;
            if (!empty($validated['observation_id'])) {
                $observation = $player->observations()->where('id', $validated['observation_id'])->first();
            }

            if ($observation) {
                $observation->update($payload);
            } else {
                $player->observations()->create($payload);
            }

            return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'observations']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'nit' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
            'nacionality' => ['required', 'integer', Rule::in(array_keys(Player::nationalityOptions()))],
            'positions' => ['required', 'array', 'min:1'],
            'positions.*' => ['integer', 'distinct', Rule::in(array_keys(Player::positionOptions()))],
            'dorsal' => 'nullable|integer|min:0',
            'foot' => ['required', 'integer', Rule::in(array_keys(Player::footOptions()))],
            'weight' => 'required|integer|min:0',
            'status' => 'nullable|boolean',
            'team_id' => ['nullable', 'uuid', Rule::exists('teams', 'id'), 'required_with:photo'],
        ]);

        $validated['status'] = $request->boolean('status') ? Player::ACTIVE : Player::INACTIVE;
        $validated['positions'] = Player::normalizePositions($validated['positions'] ?? []);
        $validated['position'] = $validated['positions'][0] ?? null;
        $teamId = $validated['team_id'] ?? null;
        unset($validated['team_id']);

        if ($request->boolean('remove_photo')) {
            $this->removePlayerPhoto($player);
        }

        $player->update($validated);
        $this->syncPlayerTeamRoster($player, $teamId);
        $this->storePlayerPhoto($player, $teamId, $request->file('photo'));

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
    }

    /**
     * Sync the player's team roster based on the provided team ID. This method checks the player's current active roster to determine the previous team association,
     * and then updates the roster records to reflect the new team association. If the team ID is empty, it will deactivate all active rosters for the player.
     * If a new team ID is provided, it will update any existing rosters that do not match the new team ID to inactive, and then create or update the roster for the new team ID to active.
     * After updating the roster records, it will also synchronize the player's storage directories to ensure that
     * the player's files are organized under the correct team folder in storage. This method is called whenever a player's team association is changed to maintain data consistency and proper file organization.
     * @param Player $player The player whose team roster is being synchronized. This should be an instance of the Player model that has already been updated with the new team association.
     * @param string|null $teamId The ID of the team to which the player is being associated. If null or empty, it indicates that the player should not be associated with any team, and all active rosters will be deactivated.
     * @return void
    */
    private function syncPlayerTeamRoster(Player $player, ?string $teamId): void
    {
        $previousTeamId = $player->rosters()
        ->where('status', PlayerRoster::ACTIVE)
        ->value('team');

        if (empty($teamId)) {
            $player->rosters()->update(['status' => PlayerRoster::INACTIVE]);
            return;
        }

        $player->rosters()
        ->where('team', '!=', $teamId)
        ->update(['status' => PlayerRoster::INACTIVE]);

        PlayerRoster::updateOrCreate(
            [
                'player' => $player->id,
                'team' => $teamId,
            ],
            [
                'position' => $player->primary_position ?? $player->position ?? 0,
                'dorsal' => $player->dorsal ?? 0,
                'status' => PlayerRoster::ACTIVE,
            ]
        );

        $this->syncPlayerStorage($player, $teamId, $previousTeamId);
    }

    private function syncPlayerStorage(Player $player, string $teamId, ?string $previousTeamId): void
    {
        $this->ensureStorageWritable();
        $disk = Storage::disk('public');

        $playerId = $player->id;
        $disk->makeDirectory(TenantStorage::path("teams/{$teamId}/players"));
        $newPath = TenantStorage::path("teams/{$teamId}/players/{$playerId}");

        if (!empty($previousTeamId) && $previousTeamId !== $teamId) {

            $oldPath = TenantStorage::path("teams/{$previousTeamId}/players/{$playerId}");

            if ($disk->exists($oldPath)) {

                if ($disk->exists($newPath)) {
                    $disk->deleteDirectory($newPath);
                }

                if (!$this->copyDirectory($disk, $oldPath, $newPath)) {
                    Log::error('Failed to copy player folder to new team.', [
                        'player' => $playerId,
                        'from' => $oldPath,
                        'to' => $newPath,
                    ]);
                    $this->flashStorageError();
                } else {
                    $this->syncPlayerPhotoPath($player, $previousTeamId, $teamId);
                }

                return;
            }
        }

        if (!$disk->exists($newPath) && !$disk->makeDirectory($newPath)) {

            Log::error('Failed to create player folder for team.', [
                'player' => $playerId,
                'team' => $teamId,
                'path' => $newPath,
            ]);

            $this->flashStorageError();
        }

        $this->ensurePlayerSubfolders($disk, $newPath);
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

    /**
     * Ensure that the storage path for player folders is writable. This method checks if the storage path exists and is writable, 
     * and if not, it logs an error with details about the path. It also flashes an error message to the session to inform the user of the issue.
     * This method is called before any operations that involve creating or moving player folders to ensure that the application can perform 
     * the necessary file system operations without encountering permission issues.
     * @return void
    */
    private function ensureStorageWritable(): void
    {
        $root = storage_path('app/public');

        if (!is_dir($root) || !is_writable($root)) {

            Log::error('Storage path is not writable for player folders.', [
                'path' => $root,
            ]);

            $this->flashStorageError();

        }
    }

    /**
     * Ensure that the necessary subfolders for a player's storage exist. This method checks for the existence of 'photos' and 'documents' 
     * subfolders within the player's main storage directory, and attempts to create them if they do not exist. If any of the subfolders 
     * cannot be created, it logs an error with details about the failure and flashes an error message to the session to inform the user of the issue. 
     * This method is called after ensuring that the main player folder exists to set up the expected directory structure for storing player-related files.
     * @param \Illuminate\Filesystem\FilesystemAdapter $disk The storage disk instance to use for checking and creating directories.
     * @param string $playerPath The path to the player's main storage directory, where the subfolders should be located. 
     * This should be in the format "teams/{teamId}/players/{playerId}".
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
     * Collect document files from player storage folders by team.
     */
    private function getPlayerDocuments(Player $player): array
    {
        $disk = Storage::disk('public');

        $teamNames = $player->rosters
            ->filter(fn (PlayerRoster $roster) => !empty($roster->team))
            ->mapWithKeys(function (PlayerRoster $roster) {
                $teamModel = $roster->relationLoaded('team') ? $roster->getRelation('team') : null;
                return [$roster->team => $teamModel?->name ?? 'Equipo'];
            })
            ->all();

        $teamIds = array_keys($teamNames);
        if (empty($teamIds)) {
            $teamIds = $this->getPlayerTeamIds($player->id);
        }

        if (empty($teamIds)) {
            return [];
        }

        $items = [];

        foreach ($teamIds as $teamId) {
            foreach (['documents', 'reports'] as $folder) {
                $basePath = TenantStorage::path("teams/{$teamId}/players/{$player->id}/{$folder}");

                if (!$disk->exists($basePath)) {
                    continue;
                }

                foreach ($disk->allFiles($basePath) as $filePath) {
                    $items[] = [
                        'name' => basename($filePath),
                        'path' => $filePath,
                        'team_id' => $teamId,
                        'team_name' => $teamNames[$teamId] ?? 'Equipo',
                        'size' => $disk->size($filePath),
                        'modified_at' => $disk->lastModified($filePath),
                    ];
                }
            }
        }

        usort($items, fn ($a, $b) => ($b['modified_at'] ?? 0) <=> ($a['modified_at'] ?? 0));

        return $items;
    }

    private function storePlayerPhoto(Player $player, ?string $teamId, $photo): void
    {
        if (!$photo || empty($teamId)) {
            return;
        }

        $this->ensureStorageWritable();
        $disk = Storage::disk('public');
        $folder = TenantStorage::path("teams/{$teamId}/players/{$player->id}/photos");

        if (!$disk->exists($folder) && !$disk->makeDirectory($folder)) {
            Log::error('Failed to create player photo folder.', [
                'player' => $player->id,
                'team' => $teamId,
                'path' => $folder,
            ]);
            $this->flashStorageError();
            return;
        }

        $filename = 'photo-' . Str::uuid() . '.' . $photo->getClientOriginalExtension();
        $path = $disk->putFileAs($folder, $photo, $filename);

        if (!$path) {
            Log::error('Failed to store player photo.', [
                'player' => $player->id,
                'team' => $teamId,
            ]);
            $this->flashStorageError();
            return;
        }

        $player->update(['photo' => $path]);
    }

    private function syncPlayerPhotoPath(Player $player, string $oldTeamId, string $newTeamId): void
    {
        if (empty($player->photo)) {
            return;
        }

        $oldPrefix = TenantStorage::path("teams/{$oldTeamId}/players/{$player->id}/");
        $newPrefix = TenantStorage::path("teams/{$newTeamId}/players/{$player->id}/");

        if (Str::startsWith($player->photo, $oldPrefix)) {
            $player->update([
                'photo' => Str::replaceFirst($oldPrefix, $newPrefix, $player->photo),
            ]);
        }
    }

    private function removePlayerPhoto(Player $player): void
    {
        if (empty($player->photo)) {
            return;
        }

        $this->ensureStorageWritable();
        $disk = Storage::disk('public');
        if ($disk->exists($player->photo) && !$disk->delete($player->photo)) {
            Log::error('Failed to delete player photo.', [
                'player' => $player->id,
                'path' => $player->photo,
            ]);
            $this->flashStorageError();
            return;
        }

        $player->update(['photo' => null]);
    }

    /**
     * Remove the player's storage directory when they are deactivated or deleted. This method retrieves all team IDs associated with 
     * the player through their rosters and attempts to delete the player's folder from each team's storage. 
     * If the storage path is not writable or if any deletion fails, it logs an error and flashes a single error 
     * message to the session to inform the user of the issue.
     * @param string $playerId The ID of the player whose storage directories should be removed.
     * @return void
    */
    private function removePlayerStorage(string $playerId): void
    {
        $this->ensureStorageWritable();
        $disk = Storage::disk('public');
        $teamIds = $this->getPlayerTeamIds($playerId);

        foreach ($teamIds as $teamId) {

            $path = TenantStorage::path("teams/{$teamId}/players/{$playerId}");

            if ($disk->exists($path) && !$disk->deleteDirectory($path)) {

                Log::error('Failed to delete player folder.', [
                    'player' => $playerId,
                    'team' => $teamId,
                    'path' => $path,
                ]);

                $this->flashStorageError();
            }

        }
    }

    /**
     * Get the IDs of teams that the player is currently or was previously associated with through rosters.
     * This method queries the PlayerRoster model to find all records for the given player ID
     * and plucks the team IDs, filtering out any empty values, ensuring uniqueness, and returning them as a simple array.
     * @param string $playerId The ID of the player for whom to retrieve associated team IDs.
     * @return array An array of unique team IDs that the player is or was associated with through rosters. 
     * If the player has no associated teams, it will return an empty array.
    */
    private function getPlayerTeamIds(string $playerId): array
    {
        return PlayerRoster::where('player', $playerId)
        ->pluck('team')
        ->filter()
        ->unique()
        ->values()
        ->all();
    }

    /** 
     * Flash an error message to the session if there was an issue with storage operations. 
     * This method ensures that the error message is only shown once per request to avoid spamming the user
     * with multiple messages if several storage operations fail. It checks if the storage error has already been shown using a boolean property, and if not,
     * it sets the property to true and flashes a single error message to the session indicating 
     * that there was a problem with creating or moving player folders and advising to check storage permissions.
    */
    private function flashStorageError(): void
    {
        if ($this->storageErrorShown) {
            return;
        }

        $this->storageErrorShown = true;
        session()->flash('error', 'No se pudieron crear o mover carpetas de jugadores. Revisa permisos de storage.');
    }

    /**
     * Store a new observation for a player.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function storeObservation(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $validated = $request->validate([
            'type' => ['required', 'integer', Rule::in(array_keys(PlayerObservation::typeOptions()))],
            'notes' => 'nullable|string',
        ]);

        $player->observations()->create([
            'type' => $validated['type'],
            'notes' => $validated['notes'] ?? null,
            'user' => Auth::id(),
            'status' => PlayerObservation::ACTIVE,
        ]);

        return redirect()->route('players.index');
    }

    /**
     * Remove a contact from a player.
     *
     * @param int $id
     * @param string $contactId
     * @return \Illuminate\Http\Response
    */
    public function destroyContact($id, $contactId)
    {
        $player = Player::findOrFail($id);
        $contact = $player->contacts()->where('id', $contactId)->firstOrFail();
        $contact->update(['status' => \App\Models\PlayerContact::INACTIVE]);

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
    }

    /**
     * Remove an observation from a player.
     *
     * @param int $id
     * @param string $observationId
     * @return \Illuminate\Http\Response
    */
    public function destroyObservation($id, $observationId)
    {
        $player = Player::findOrFail($id);
        $observation = $player->observations()->where('id', $observationId)->firstOrFail();
        $observation->update(['status' => PlayerObservation::INACTIVE]);

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'observations']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $player->update(['status' => Player::INACTIVE]);
        $player->contacts()->update(['status' => \App\Models\PlayerContact::INACTIVE]);
        $player->observations()->update(['status' => PlayerObservation::INACTIVE]);
        $this->removePlayerStorage($player->id);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('players.index');
    }

    /**
     * Activate a player.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
    */
    public function activate($id)
    {
        $player = Player::findOrFail($id);
        $player->update(['status' => Player::ACTIVE]);
        $player->contacts()->update(['status' => \App\Models\PlayerContact::ACTIVE]);
        $player->observations()->update(['status' => PlayerObservation::ACTIVE]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('players.index');
    }

    /**
     * Activate a contact for a player.
     *
     * @param int $id
     * @param string $contactId
     * @return \Illuminate\Http\Response
    */
    public function activateContact($id, $contactId)
    {
        $player = Player::findOrFail($id);
        $contact = $player->contacts()->where('id', $contactId)->firstOrFail();
        $contact->update(['status' => \App\Models\PlayerContact::ACTIVE]);

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
    }

    /**
     * Activate an observation for a player.
     *
     * @param int $id
     * @param string $observationId
     * @return \Illuminate\Http\Response
    */
    public function activateObservation($id, $observationId)
    {
        $player = Player::findOrFail($id);
        $observation = $player->observations()->where('id', $observationId)->firstOrFail();
        $observation->update(['status' => PlayerObservation::ACTIVE]);

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'observations']);
    }
}
