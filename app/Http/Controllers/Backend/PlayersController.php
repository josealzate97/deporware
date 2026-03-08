<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerObservation;
use App\Models\PlayerRoster;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayersController extends Controller
{   
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

        $teamOptions = Team::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $playersQuery = Player::with([
            'rosters' => function ($query) {
                $query->with('team')
                    ->orderByDesc('status')
                    ->orderByDesc('created_at');
            },
        ]);

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
            $playersQuery->where('position', (int) $position);
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
        $teamOptions = Team::query()
            ->where('status', Team::ACTIVE)
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
            'nit' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
            'nacionality' => ['required', 'integer', Rule::in(array_keys(Player::nationalityOptions()))],
            'position' => ['nullable', 'integer', Rule::in(array_keys(Player::positionOptions()))],
            'dorsal' => 'nullable|integer|min:0',
            'foot' => ['required', 'integer', Rule::in(array_keys(Player::footOptions()))],
            'weight' => 'required|integer|min:0',
            'status' => 'nullable|boolean',
            'team_id' => ['nullable', 'uuid', Rule::exists('teams', 'id')],
            'initial_observation_type' => ['nullable', 'integer', Rule::in(array_keys(PlayerObservation::typeOptions()))],
            'initial_observation_notes' => 'nullable|string',
        ]);

        $validated['status'] = $request->boolean('status') ? Player::ACTIVE : Player::INACTIVE;

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
        $player = Player::with(['contacts', 'observations.user'])->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.players.show-modal', [
                'player' => $player,
                'observationTypes' => PlayerObservation::typeOptions(),
            ]);
        }

        return redirect()->route('players.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $player = Player::with(['contacts', 'observations.user', 'rosters'])->findOrFail($id);

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
            $validated = $request->validate([
                'contact_id' => 'nullable|uuid',
                'contact_name' => 'required|string|max:100',
                'contact_lastname' => 'required|string|max:100',
                'contact_email' => 'required|email|max:100',
                'contact_phone' => 'required|string|max:20',
                'contact_address' => 'required|string|max:80',
                'contact_city' => 'required|string|max:80',
                'contact_status' => 'nullable|boolean',
            ]);

            $contactPayload = [
                'name' => $validated['contact_name'],
                'lastname' => $validated['contact_lastname'],
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
            'nit' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
            'nacionality' => ['required', 'integer', Rule::in(array_keys(Player::nationalityOptions()))],
            'position' => ['nullable', 'integer', Rule::in(array_keys(Player::positionOptions()))],
            'dorsal' => 'nullable|integer|min:0',
            'foot' => ['required', 'integer', Rule::in(array_keys(Player::footOptions()))],
            'weight' => 'required|integer|min:0',
            'status' => 'nullable|boolean',
            'team_id' => ['nullable', 'uuid', Rule::exists('teams', 'id')],
        ]);

        $validated['status'] = $request->boolean('status') ? Player::ACTIVE : Player::INACTIVE;
        $teamId = $validated['team_id'] ?? null;
        unset($validated['team_id']);

        $player->update($validated);
        $this->syncPlayerTeamRoster($player, $teamId);

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
    }

    private function syncPlayerTeamRoster(Player $player, ?string $teamId): void
    {
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
                'position' => $player->position ?? 0,
                'dorsal' => $player->dorsal ?? 0,
                'status' => PlayerRoster::ACTIVE,
            ]
        );
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
