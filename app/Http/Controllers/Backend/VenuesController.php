<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SportsVenue;
use Illuminate\Http\Request;

class VenuesController extends Controller
{   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $search = trim((string) request()->query('search', ''));
        $status = (string) request()->query('status', '');
        $statusOptions = [
            '1' => 'Activas',
            '0' => 'Inactivas',
        ];

        if ($status !== '' && !array_key_exists($status, $statusOptions)) {
            $status = '';
        }

        $venuesQuery = SportsVenue::query()
            ->orderByDesc('status')
            ->orderBy('name');

        if ($search !== '') {
            $venuesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '') {
            $venuesQuery->where('status', (int) $status);
        }

        return view('backend.venues.index', [
            'venues' => $venuesQuery->paginate(10)->withQueryString(),
            'statusOptions' => $statusOptions,
            'search' => $search,
            'selectedStatus' => $status,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('backend.venues.new', [
            'isEdit' => false,
            'venue' => new SportsVenue(),
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
            'address' => 'required|string|max:250',
            'city' => 'required|string|max:100',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        SportsVenue::create($data);

        return redirect()->route('venues.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $venue = SportsVenue::with('teams')->findOrFail($id);

        if (request()->boolean('modal')) {
            return view('backend.venues.show-modal', [
                'venue' => $venue,
            ]);
        }

        return redirect()->route('venues.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        return view('backend.venues.new', [
            'isEdit' => true,
            'venue' => SportsVenue::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:250',
            'city' => 'required|string|max:100',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        $venue = SportsVenue::findOrFail($id);
        $venue->update($data);

        return redirect()->route('venues.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $venue = SportsVenue::findOrFail($id);

        if ($venue->name === 'Sede Principal') {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'La Sede Principal no se puede eliminar.',
                ], 403);
            }

            return redirect()->route('venues.index');
        }

        $venue->status = false;
        $venue->save();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Sede marcada como inactiva.',
                'venue' => $venue,
            ]);
        }

        return redirect()->route('venues.index');
    }

    /**
     * Activate a venue.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
    */
    public function activate($id)
    {
        $venue = SportsVenue::findOrFail($id);
        $venue->status = true;
        $venue->save();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Sede activada.',
                'venue' => $venue,
            ]);
        }

        return redirect()->route('venues.index');
    }
}
