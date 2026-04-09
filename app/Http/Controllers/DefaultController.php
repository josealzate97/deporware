<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\MatchModel;
use App\Models\Player;
use App\Models\PlayerRoster;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DefaultController extends Controller
{
    /**
     * Mostrar dashboard principal del sistema.
     */
    public function dashboard()
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $configuration = Configuration::query()->latest('created_at')->first();

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        // Scoping: null = sin restricción (root/sport_manager), Collection = equipos visibles
        $scopedTeamIds = auth()->user()->scopedTeamIds();
        $isScoped = $scopedTeamIds !== null;

        $activePlayers = Player::where('status', Player::ACTIVE)
            ->when($isScoped, function ($q) use ($scopedTeamIds) {
                $q->whereHas('rosters', fn($r) => $r->whereIn('team', $scopedTeamIds));
            })
            ->count();

        $activeTeams = Team::where('status', Team::ACTIVE)
            ->when($isScoped, fn($q) => $q->whereIn('id', $scopedTeamIds))
            ->count();

        $activeUsers = User::where('status', User::ACTIVE)->count();
        $activeVenues = SportsVenue::where('status', true)->count();

        $scheduledMatches = MatchModel::with(['team', 'rival'])
        ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
        ->where('match_date', '>=', $now)
        ->orderBy('match_date')
        ->limit(4)
        ->get();

        $upcomingTrainings = Training::with('team')
        ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
        ->where('created_at', '>=', $now)
        ->where('status', Training::ACTIVE)
        ->orderBy('created_at')
        ->limit(4)
        ->get();

        $upcomingAgenda = $this->buildUpcomingAgenda($scheduledMatches, $upcomingTrainings);

        $recentMatches = MatchModel::with(['team', 'rival'])
        ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
        ->orderByDesc('match_date')
        ->limit(5)
        ->get();

        $recentTrainings = Training::with('team')
        ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

        $teamRosterLoad = Team::query()
        ->when($isScoped, fn($q) => $q->whereIn('id', $scopedTeamIds))
        ->orderBy('name')
        ->get()
        ->map(function (Team $team) {
            $playerCount = PlayerRoster::where('team', $team->id)
                ->where('status', PlayerRoster::ACTIVE)
                ->count();

            return [
                'id' => $team->id,
                'name' => $team->name,
                'season' => $team->season,
                'player_count' => $playerCount,
                'status' => (int) $team->status,
            ];
        })
        ->sortByDesc('player_count')
        ->take(6)
        ->values();

        $monthlyActivity = collect(range(5, 0))->map(function (int $monthsAgo) use ($now, $isScoped, $scopedTeamIds) {

            $date = $now->copy()->startOfMonth()->subMonths($monthsAgo);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            return [
                'key' => $date->format('Y-m'),
                'label' => ucfirst($date->locale('es')->translatedFormat('M')),
                'monthLabel' => ucfirst($date->locale('es')->translatedFormat('F Y')),
                'matches' => MatchModel::whereBetween('match_date', [$start, $end])
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count(),
                'trainings' => Training::whereBetween('created_at', [$start, $end])
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count(),
            ];

        })->values();

        $summaryCards = [
            [
                'label' => 'Jugadores activos',
                'value' => $activePlayers,
                'icon' => 'fa-solid fa-user-group',
                'tone' => 'primary',
                'meta' => PlayerRoster::where('status', PlayerRoster::ACTIVE)
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count() . ' registros de roster',
            ],
            [
                'label' => 'Equipos activos',
                'value' => $activeTeams,
                'icon' => 'fa-solid fa-shield-halved',
                'tone' => 'success',
                'meta' => $activeVenues . ' sedes activas',
            ],
            [
                'label' => 'Partidos del mes',
                'value' => MatchModel::whereBetween('match_date', [$monthStart, $monthEnd])
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count(),
                'icon' => 'fa-solid fa-futbol',
                'tone' => 'warning',
                'meta' => MatchModel::where('match_status', MatchModel::STATUS_COMPLETED)
                    ->whereBetween('match_date', [$monthStart, $monthEnd])
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count() . ' completados',
            ],
            [
                'label' => 'Entrenamientos del mes',
                'value' => Training::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count(),
                'icon' => 'fa-solid fa-dumbbell',
                'tone' => 'accent',
                'meta' => Training::where('status', Training::ACTIVE)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->when($isScoped, fn($q) => $q->whereIn('team', $scopedTeamIds))
                    ->count() . ' activos',
            ],
        ];

        return view('backend.home', [
            'configuration' => $configuration,            'tenant'        => $tenant,            'summaryCards' => $summaryCards,
            'activeUsers' => $activeUsers,
            'upcomingAgenda' => $upcomingAgenda,
            'recentMatches' => $recentMatches,
            'recentTrainings' => $recentTrainings,
            'teamRosterLoad' => $teamRosterLoad,
            'monthlyActivity' => $monthlyActivity,
        ]);
    }

    private function buildUpcomingAgenda(Collection $matches, Collection $trainings): Collection
    {
        $matchItems = $matches->map(function (MatchModel $match) {

            $teamModel = $match->relationLoaded('team') ? $match->getRelation('team') : null;
            $rivalModel = $match->relationLoaded('rival') ? $match->getRelation('rival') : null;

            return [
                'type' => 'match',
                'title' => ($teamModel?->name ?? 'Equipo') . ' vs ' . ($rivalModel?->name ?? 'Rival'),
                'subtitle' => 'Partido programado',
                'datetime' => $match->match_date,
                'badge' => MatchModel::statusOptions()[$match->match_status] ?? 'Sin estado',
                'icon' => 'fa-solid fa-futbol',
                'route' => route('matches.index', ['view' => 'calendar', 'month' => $match->match_date?->format('Y-m')]),
            ];

        });

        $trainingItems = $trainings->map(function (Training $training) {

            $teamModel = $training->relationLoaded('team') ? $training->getRelation('team') : null;

            return [
                'type' => 'training',
                'title' => $training->name ?: 'Entrenamiento',
                'subtitle' => $teamModel?->name ?? 'Sesión programada',
                'datetime' => $training->created_at,
                'badge' => Training::statusOptions()[$training->status] ?? 'Sin estado',
                'icon' => 'fa-solid fa-dumbbell',
                'route' => route('trainings.index', ['view' => 'calendar', 'month' => $training->created_at?->format('Y-m')]),
            ];

        });

        return $matchItems
        ->concat($trainingItems)
        ->sortBy('datetime')
        ->take(6)
        ->values();
    }
}
