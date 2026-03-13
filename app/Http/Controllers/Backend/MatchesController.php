<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AttackPoint;
use App\Models\DefensivePoint;
use App\Models\MatchModel;
use App\Models\RivalTeam;
use App\Models\SportsVenue;
use App\Models\Team;
use Illuminate\Http\Request;

class MatchesController extends Controller
{   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $matches = MatchModel::with(['team', 'rival'])
            ->orderByDesc('match_status')
            ->orderBy('match_date')
            ->get();

        return view('backend.matches.index', [
            'matches' => $matches,
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
        return view('backend.matches.new', [
            'isEdit' => true,
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
