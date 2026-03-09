<?php

namespace App\Http\Controllers\Backend\Configurations;

use App\Http\Controllers\Controller;
use App\Models\AttackPoint;
use App\Models\DefensivePoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PointsController extends Controller
{
    public function index()
    {
        $attackSearch = trim((string) request()->query('attack_search', ''));
        $defensiveSearch = trim((string) request()->query('defensive_search', ''));

        $attackQuery = AttackPoint::query()
            ->where('status', AttackPoint::ACTIVE)
            ->orderBy('name');

        if ($attackSearch !== '') {
            $attackQuery->where('name', 'like', '%' . $attackSearch . '%');
        }

        $defensiveQuery = DefensivePoint::query()
            ->where('status', DefensivePoint::ACTIVE)
            ->orderBy('name');

        if ($defensiveSearch !== '') {
            $defensiveQuery->where('name', 'like', '%' . $defensiveSearch . '%');
        }

        return view('backend.configurations.points.index', [
            'attackPoints' => $attackQuery
                ->paginate(8, ['*'], 'attack_page')
                ->appends([
                    'attack_search' => $attackSearch,
                    'defensive_search' => $defensiveSearch,
                    'defensive_page' => request()->query('defensive_page'),
                ]),
            'defensivePoints' => $defensiveQuery
                ->paginate(8, ['*'], 'defensive_page')
                ->appends([
                    'attack_search' => $attackSearch,
                    'defensive_search' => $defensiveSearch,
                    'attack_page' => request()->query('attack_page'),
                ]),
            'attackSearch' => $attackSearch,
            'defensiveSearch' => $defensiveSearch,
        ]);
    }

    public function createAttack()
    {
        return view('backend.configurations.points.form', [
            'isEdit' => false,
            'pointType' => 'attack',
            'point' => new AttackPoint(),
        ]);
    }

    public function storeAttack(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:attack_points,name',
        ]);

        AttackPoint::create($data);

        return redirect()->route('configurations.points.index');
    }

    public function editAttack(string $id)
    {
        return view('backend.configurations.points.form', [
            'isEdit' => true,
            'pointType' => 'attack',
            'point' => AttackPoint::findOrFail($id),
        ]);
    }

    public function updateAttack(Request $request, string $id)
    {
        $point = AttackPoint::findOrFail($id);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('attack_points', 'name')->ignore($point->id),
            ],
        ]);

        $point->update($data);

        return redirect()->route('configurations.points.index');
    }

    public function destroyAttack(string $id)
    {
        AttackPoint::findOrFail($id)->delete();

        return redirect()->route('configurations.points.index');
    }

    public function createDefensive()
    {
        return view('backend.configurations.points.form', [
            'isEdit' => false,
            'pointType' => 'defensive',
            'point' => new DefensivePoint(),
        ]);
    }

    public function storeDefensive(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:defensive_points,name',
        ]);

        DefensivePoint::create($data);

        return redirect()->route('configurations.points.index');
    }

    public function editDefensive(string $id)
    {
        return view('backend.configurations.points.form', [
            'isEdit' => true,
            'pointType' => 'defensive',
            'point' => DefensivePoint::findOrFail($id),
        ]);
    }

    public function updateDefensive(Request $request, string $id)
    {
        $point = DefensivePoint::findOrFail($id);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('defensive_points', 'name')->ignore($point->id),
            ],
        ]);

        $point->update($data);

        return redirect()->route('configurations.points.index');
    }

    public function destroyDefensive(string $id)
    {
        DefensivePoint::findOrFail($id)->delete();

        return redirect()->route('configurations.points.index');
    }
}
