<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerObservation;
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
    public function index()
    {
        $players = Player::orderByDesc('status')
            ->orderBy('name')
            ->orderBy('lastname')
            ->get();

        return view('backend.players.index', [
            'players' => $players,
            'observationTypes' => PlayerObservation::typeOptions(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('backend.players.new', [
            'isEdit' => false,
            'player' => new Player(),
            'nationalityOptions' => Player::nationalityOptions(),
            'positionOptions' => Player::positionOptions(),
            'footOptions' => Player::footOptions(),
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
        ]);

        $validated['status'] = $request->boolean('status') ? Player::ACTIVE : Player::INACTIVE;

        $player = Player::create($validated);

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
        $player = Player::with(['contacts', 'observations.user'])->findOrFail($id);

        return view('backend.players.new', [
            'isEdit' => true,
            'player' => $player,
            'nationalityOptions' => Player::nationalityOptions(),
            'positionOptions' => Player::positionOptions(),
            'footOptions' => Player::footOptions(),
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

            $contact = $player->contacts()->first();
            if ($contact) {
                $contact->update($contactPayload);
            } else {
                $player->contacts()->create($contactPayload);
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
        ]);

        $validated['status'] = $request->boolean('status') ? Player::ACTIVE : Player::INACTIVE;

        $player->update($validated);

        return redirect()->route('players.edit', ['id' => $player->id, 'step' => 'contacts']);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        return redirect()->route('players.index');
    }
}
