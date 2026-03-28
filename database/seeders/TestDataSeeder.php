<?php

namespace Database\Seeders;

use App\Models\ManagerRoster;
use App\Models\MatchFeedback;
use App\Models\MatchModel;
use App\Models\MatchTeamRating;
use App\Models\PlayerContact;
use App\Models\Player;
use App\Models\PlayerObservation;
use App\Models\PlayerRoster;
use App\Models\RivalTeam;
use App\Models\AttackPoint;
use App\Models\DefensivePoint;
use App\Models\SportsVenue;
use App\Models\Team;
use App\Models\TeamVenue;
use App\Models\User;
use App\Models\UserVenue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TestDataSeeder extends Seeder
{
    private array $firstNames = [
        'Santiago', 'Mateo', 'Sebastián', 'Matías', 'Nicolás', 'Samuel', 'Daniel', 'Alejandro',
        'Diego', 'Tomás', 'Juan', 'David', 'Miguel', 'José', 'Carlos', 'Andrés', 'Felipe',
        'Gabriel', 'Lucas', 'Emiliano', 'Joaquín', 'Martín', 'Emanuel', 'Pablo', 'Cristian',
        'Kevin', 'Brandon', 'Julián', 'Óscar', 'Sergio', 'Ricardo', 'Camilo', 'Leonardo',
    ];

    private array $lastNames = [
        'García', 'Rodríguez', 'Martínez', 'López', 'González', 'Hernández', 'Pérez', 'Sánchez',
        'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Morales', 'Reyes', 'Cruz',
        'Ortiz', 'Gutiérrez', 'Chávez', 'Ramos', 'Vargas', 'Castillo', 'Jiménez', 'Moreno',
        'Romero', 'Herrera', 'Medina', 'Aguilar', 'Castro', 'Vega', 'Ruiz', 'Mendoza',
    ];

    private array $playerPhotos = [
        'images/branding/logo_half.png',
    ];

    private array $matchFormations = [
        '4-3-3', '4-4-2', '4-2-3-1', '3-5-2', '3-4-3', '4-1-4-1', '5-3-2',
    ];

    public function run(): void
    {
        // Crear 10 sedes deportivas
        $venues = $this->createVenues();
        $this->command->info('✓ 10 Sedes deportivas creadas');

        // Crear 10 usuarios
        $users = $this->createUsers();
        $this->command->info('✓ 10 Usuarios creados');

        // Asignar usuarios a sedes
        $this->assignUsersToVenues($users, $venues);
        $this->command->info('✓ Usuarios asignados a sedes');

        // Crear equipos rivales
        $rivals = $this->createRivalTeams();
        $this->command->info('✓ ' . count($rivals) . ' Equipos rivales creados');

        // Crear jugadores
        $players = $this->createPlayers();
        $this->command->info('✓ ' . count($players) . ' Jugadores creados');

        // Crear contactos y observaciones para cada jugador
        $this->createPlayerContactsAndObservations($players, $users);
        $this->command->info('✓ Contactos y observaciones de jugadores creados');

        // Crear equipos con sus relaciones
        $teams = $this->createTeamsWithRelations($users, $venues, $players);
        $this->command->info('✓ ' . count($teams) . ' Equipos creados con sus relaciones');

        // Obtener puntos para feedback de partidos
        $attackPoints = AttackPoint::pluck('id')->all();
        $defensivePoints = DefensivePoint::pluck('id')->all();

        // Crear partidos
        $matches = $this->createMatches($teams, $rivals, $venues, $attackPoints, $defensivePoints);
        $this->command->info('✓ ' . count($matches) . ' Partidos creados');

        $this->command->newLine();
        $this->command->info('=== RESUMEN DE DATOS CREADOS ===');
        $this->command->table(
            ['Entidad', 'Cantidad'],
            [
                ['Sedes Deportivas', count($venues)],
                ['Usuarios', count($users)],
                ['Equipos Rivales', count($rivals)],
                ['Jugadores', count($players)],
                ['Equipos', count($teams)],
                ['Partidos', count($matches)],
                ['Match Feedback', MatchFeedback::count()],
                ['Match Team Ratings', MatchTeamRating::count()],
                ['Manager Roster', ManagerRoster::count()],
                ['Player Roster', PlayerRoster::count()],
                ['Player Contacts', PlayerContact::count()],
                ['Player Observations', PlayerObservation::count()],
                ['Team-Venue', TeamVenue::count()],
                ['User-Venue', UserVenue::count()],
            ]
        );
    }

    private function createVenues(): array
    {
        $venuesData = [
            ['name' => 'Estadio Municipal El Campín', 'address' => 'Calle 57 # 30-20', 'city' => 'Bogotá'],
            ['name' => 'Coliseo Deportivo Norte', 'address' => 'Carrera 15 # 120-45', 'city' => 'Bogotá'],
            ['name' => 'Cancha Sintética La Victoria', 'address' => 'Avenida Boyacá # 80-10', 'city' => 'Bogotá'],
            ['name' => 'Polideportivo Sur', 'address' => 'Calle 40 Sur # 25-30', 'city' => 'Bogotá'],
            ['name' => 'Centro Deportivo Usaquén', 'address' => 'Carrera 7 # 140-25', 'city' => 'Bogotá'],
            ['name' => 'Estadio Atanasio Girardot', 'address' => 'Calle 48 # 73-10', 'city' => 'Medellín'],
            ['name' => 'Unidad Deportiva Belén', 'address' => 'Carrera 76 # 18-120', 'city' => 'Medellín'],
            ['name' => 'Estadio Pascual Guerrero', 'address' => 'Calle 5 # 42-00', 'city' => 'Cali'],
            ['name' => 'Coliseo El Pueblo', 'address' => 'Avenida Roosevelt # 35-20', 'city' => 'Cali'],
            ['name' => 'Estadio Metropolitano', 'address' => 'Vía 40 # 79-260', 'city' => 'Barranquilla'],
        ];

        $venues = [];
        foreach ($venuesData as $data) {
            $venues[] = SportsVenue::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'status' => true,
            ]);
        }

        return $venues;
    }

    private function createUsers(): array
    {
        $usersData = [
            ['name' => 'Carlos Martínez', 'email' => 'carlos.martinez@deporware.com', 'role' => User::ROLE_SPORT_MANAGER],
            ['name' => 'María García', 'email' => 'maria.garcia@deporware.com', 'role' => User::ROLE_COORDINATOR],
            ['name' => 'Juan Rodríguez', 'email' => 'juan.rodriguez@deporware.com', 'role' => User::ROLE_COACH],
            ['name' => 'Ana López', 'email' => 'ana.lopez@deporware.com', 'role' => User::ROLE_COACH],
            ['name' => 'Pedro Sánchez', 'email' => 'pedro.sanchez@deporware.com', 'role' => User::ROLE_COORDINATOR],
            ['name' => 'Laura Hernández', 'email' => 'laura.hernandez@deporware.com', 'role' => User::ROLE_COACH],
            ['name' => 'Diego Torres', 'email' => 'diego.torres@deporware.com', 'role' => User::ROLE_SPORT_MANAGER],
            ['name' => 'Sofía Ramírez', 'email' => 'sofia.ramirez@deporware.com', 'role' => User::ROLE_COACH],
            ['name' => 'Andrés Morales', 'email' => 'andres.morales@deporware.com', 'role' => User::ROLE_COORDINATOR],
            ['name' => 'Valentina Castro', 'email' => 'valentina.castro@deporware.com', 'role' => User::ROLE_COACH],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $username = strtolower(str_replace(' ', '.', $data['name']));
            $users[] = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => '3' . rand(100000000, 199999999),
                'username' => $username,
                'role' => $data['role'],
                'hired_date' => now()->subMonths(rand(1, 24)),
                'password' => Hash::make('password123'),
                'status' => User::ACTIVE,
            ]);
        }

        return $users;
    }

    private function assignUsersToVenues(array $users, array $venues): void
    {
        foreach ($users as $user) {
            $numVenues = rand(2, 3);
            $selectedVenues = array_rand(array_flip(array_map(fn($v) => $v->id, $venues)), $numVenues);
            
            if (!is_array($selectedVenues)) {
                $selectedVenues = [$selectedVenues];
            }

            foreach ($selectedVenues as $venueId) {
                UserVenue::create([
                    'user' => $user->id,
                    'venue' => $venueId,
                    'status' => UserVenue::ACTIVE,
                ]);
            }
        }
    }

    private function createRivalTeams(): array
    {
        $rivalNames = [
            'Millonarios FC', 'Atlético Nacional', 'América de Cali', 'Deportivo Cali',
            'Junior FC', 'Santa Fe', 'Once Caldas', 'Deportes Tolima', 'Envigado FC',
            'Alianza Petrolera', 'Jaguares FC', 'Patriotas FC', 'La Equidad', 'Águilas Doradas',
            'Boyacá Chicó', 'Deportivo Pereira', 'Atlético Bucaramanga', 'Cortuluá',
        ];

        $rivals = [];
        foreach ($rivalNames as $name) {
            $rivals[] = RivalTeam::create([
                'name' => $name,
                'status' => RivalTeam::ACTIVE,
            ]);
        }

        return $rivals;
    }

    private function createPlayers(): array
    {
        $players = [];
        $positions = [
            Player::POSICION_ARQUERO,
            Player::POSICION_DEFENSA_CENTRAL,
            Player::POSICION_LATERAL_DERECHO,
            Player::POSICION_LATERAL_IZQUIERDO,
            Player::POSICION_MEDIOCAMPISTA_DEFENSIVO,
            Player::POSICION_MEDIOCAMPISTA_CENTRAL,
            Player::POSICION_MEDIOCAMPISTA_OFENSIVO,
            Player::POSICION_EXTREMO_DERECHO,
            Player::POSICION_EXTREMO_IZQUIERDO,
            Player::POSICION_DELANTERO_CENTRO,
            Player::POSICION_SEGUNDA_PUNTA,
        ];

        // Crear 100 jugadores
        for ($i = 0; $i < 100; $i++) {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
            $position = $positions[array_rand($positions)];
            
            // Generar año de nacimiento entre 2006-2012 (jugadores juveniles)
            $birthYear = rand(2006, 2012);
            $birthDate = sprintf('%d-%02d-%02d', $birthYear, rand(1, 12), rand(1, 28));

            $players[] = Player::create([
                'name' => $firstName,
                'lastname' => $lastName,
                'photo' => $this->playerPhotos[array_rand($this->playerPhotos)],
                'nit' => '1' . rand(100000000, 999999999),
                'email' => strtolower($firstName . '.' . explode(' ', $lastName)[0] . $i . '@email.com'),
                'phone' => '3' . rand(100000000, 199999999),
                'birthdate' => $birthDate,
                'nacionality' => Player::NATIONALITY_COLOMBIA,
                'position' => $position,
                'positions' => [$position],
                'dorsal' => null, // Se asigna en el roster
                'foot' => rand(1, 3),
                'weight' => rand(45, 75),
                'status' => Player::ACTIVE,
            ]);
        }

        return $players;
    }

    private function createPlayerContactsAndObservations(array $players, array $users): void
    {
        $relationships = [
            PlayerContact::MOTHER,
            PlayerContact::FATHER,
            PlayerContact::SIBLING,
            PlayerContact::UNCLE_AUNT,
            PlayerContact::COUSIN,
        ];

        $cities = ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Bucaramanga', 'Pereira'];

        foreach ($players as $player) {
            $numContacts = rand(2, 3);
            for ($i = 0; $i < $numContacts; $i++) {
                $firstName = $this->firstNames[array_rand($this->firstNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];
                $city = $cities[array_rand($cities)];

                PlayerContact::create([
                    'name' => $firstName,
                    'lastname' => $lastName,
                    'relationship' => $relationships[array_rand($relationships)],
                    'email' => strtolower($firstName . '.' . $lastName . rand(1, 99) . '@contacto.com'),
                    'phone' => '3' . rand(100000000, 199999999),
                    'address' => 'Calle ' . rand(1, 120) . ' # ' . rand(1, 80) . '-' . rand(1, 50),
                    'city' => $city,
                    'player' => $player->id,
                    'status' => PlayerContact::ACTIVE,
                ]);
            }

            $author = $users[array_rand($users)];
            $observationTypes = [
                PlayerObservation::TYPE_TACTIC,
                PlayerObservation::TYPE_PSYCHIQUE,
                PlayerObservation::TYPE_PSYCOLOGICAL,
            ];

            PlayerObservation::create([
                'player' => $player->id,
                'type' => $observationTypes[array_rand($observationTypes)],
                'notes' => 'Observacion de seguimiento tecnico y actitudinal del jugador.',
                'user' => $author->id,
                'status' => PlayerObservation::ACTIVE,
            ]);
        }
    }

    private function createTeamsWithRelations(array $users, array $venues, array $players): array
    {
        $teamsData = [
            ['name' => 'Águilas Doradas Sub-17', 'year' => '2008-2009', 'type' => Team::TYPE_COMPETITIVE, 'season' => '2026-A'],
            ['name' => 'Leones del Norte Sub-15', 'year' => '2010-2011', 'type' => Team::TYPE_COMPETITIVE, 'season' => '2026-A'],
            ['name' => 'Tigres FC Sub-13', 'year' => '2012-2013', 'type' => Team::TYPE_FORMATIVE, 'season' => '2026-A'],
            ['name' => 'Halcones Sub-19', 'year' => '2006-2007', 'type' => Team::TYPE_COMPETITIVE, 'season' => '2026-A'],
            ['name' => 'Panteras Junior Sub-11', 'year' => '2014-2015', 'type' => Team::TYPE_FORMATIVE, 'season' => '2026-A'],
        ];

        $teams = [];
        $coaches = array_values(array_filter($users, fn($u) => $u->role === User::ROLE_COACH));
        $coachIndex = 0;
        $playerIndex = 0;

        foreach ($teamsData as $teamData) {
            $team = Team::create([
                'name' => $teamData['name'],
                'year' => $teamData['year'],
                'type' => $teamData['type'],
                'season' => $teamData['season'],
                'status' => Team::ACTIVE,
            ]);

            // Asignar entrenador principal
            if (isset($coaches[$coachIndex])) {
                ManagerRoster::create([
                    'user' => $coaches[$coachIndex]->id,
                    'team' => $team->id,
                    'role' => ManagerRoster::ROLE_PRIMARY_COACH,
                    'status' => 1,
                ]);
                $coachIndex = ($coachIndex + 1) % count($coaches);
            }

            // Asignar entrenador asistente
            if (isset($coaches[$coachIndex])) {
                ManagerRoster::create([
                    'user' => $coaches[$coachIndex]->id,
                    'team' => $team->id,
                    'role' => ManagerRoster::ROLE_ASSISTANT_COACH,
                    'status' => 1,
                ]);
                $coachIndex = ($coachIndex + 1) % count($coaches);
            }

            // Asignar 2-3 sedes al equipo
            $numVenues = rand(2, 3);
            $teamVenues = array_slice($venues, rand(0, count($venues) - $numVenues), $numVenues);
            foreach ($teamVenues as $venue) {
                TeamVenue::create([
                    'team' => $team->id,
                    'venue' => $venue->id,
                    'status' => TeamVenue::ACTIVE,
                ]);
            }

            // Asignar 20 jugadores al equipo
            $dorsalUsed = [];
            for ($p = 0; $p < 20 && $playerIndex < count($players); $p++) {
                $player = $players[$playerIndex];
                
                // Generar dorsal único para este equipo
                do {
                    $dorsal = rand(1, 99);
                } while (in_array($dorsal, $dorsalUsed));
                $dorsalUsed[] = $dorsal;

                PlayerRoster::create([
                    'player' => $player->id,
                    'team' => $team->id,
                    'position' => $player->position,
                    'dorsal' => $dorsal,
                    'status' => PlayerRoster::ACTIVE,
                ]);

                $playerIndex++;
            }

            $teams[] = $team;
        }

        return $teams;
    }

    private function createMatches(
        array $teams,
        array $rivals,
        array $venues,
        array $attackPoints,
        array $defensivePoints
    ): array
    {
        $matches = [];

        foreach ($teams as $team) {
            // Crear 8 partidos por equipo (una temporada típica)
            for ($i = 0; $i < 8; $i++) {
                $rival = $rivals[array_rand($rivals)];
                $venue = $venues[array_rand($venues)];
                $isPast = rand(1, 100) <= 70;
                $matchDate = $isPast
                    ? now()->subDays(rand(1, 90))
                    : now()->addDays(rand(1, 45));
                $side = rand(1, 2); // Local o visitante
                
                // Determinar estado del partido
                $status = $isPast ? MatchModel::STATUS_COMPLETED : MatchModel::STATUS_SCHEDULED;
                
                // Si está completado, generar resultado
                $result = null;
                $finalScore = null;
                if ($status === MatchModel::STATUS_COMPLETED) {
                    $result = rand(1, 3); // Ganar, perder o empatar
                    $goalsTeam = rand(0, 5);
                    $goalsRival = rand(0, 5);
                    
                    // Ajustar goles según resultado
                    if ($result === MatchModel::RESULT_WIN) {
                        $goalsTeam = max($goalsTeam, $goalsRival + 1);
                    } elseif ($result === MatchModel::RESULT_LOSS) {
                        $goalsRival = max($goalsRival, $goalsTeam + 1);
                    } else {
                        $goalsRival = $goalsTeam;
                    }
                    
                    $finalScore = $goalsTeam . '-' . $goalsRival;
                }

                $match = MatchModel::create([
                    'match_date' => $matchDate,
                    'match_round' => 'Jornada ' . ($i + 1),
                    'team' => $team->id,
                    'rival' => $rival->id,
                    'venue' => $venue->id,
                    'location' => $venue->address . ', ' . $venue->city,
                    'match_status' => $status,
                    'match_result' => $result,
                    'side' => $side,
                    'final_score' => $finalScore,
                    'match_notes' => $isPast ? 'Partido disputado en la ' . ($i + 1) . 'ª jornada de la temporada.' : null,
                ]);

                if ($status === MatchModel::STATUS_COMPLETED) {
                    $docxPath = 'matches/' . $match->id . '.docx';
                    $docContent = "Reporte de partido\nEquipo: {$team->name}\nRival: {$rival->name}\nFecha: {$matchDate}\nResultado: {$finalScore}\n";
                    Storage::disk('public')->put($docxPath, $docContent);
                    $match->update(['match_file' => $docxPath]);

                    MatchFeedback::create([
                        'match' => $match->id,
                        'match_formation' => $this->matchFormations[array_rand($this->matchFormations)],
                        'attack_strengths' => $attackPoints ? $attackPoints[array_rand($attackPoints)] : null,
                        'attack_weaknesses' => $attackPoints ? $attackPoints[array_rand($attackPoints)] : null,
                        'defense_strengths' => $defensivePoints ? $defensivePoints[array_rand($defensivePoints)] : null,
                        'defense_weaknesses' => $defensivePoints ? $defensivePoints[array_rand($defensivePoints)] : null,
                        'notes' => 'Evaluacion general del rendimiento colectivo y puntos a mejorar.',
                    ]);

                    MatchTeamRating::create([
                        'match' => $match->id,
                        'referee_rating' => rand(1, 5),
                        'coach_rating' => rand(1, 5),
                        'teammates_rating' => rand(1, 5),
                        'opponents_rating' => rand(1, 5),
                        'fans_rating' => rand(1, 5),
                        'notes' => 'Valoracion posterior al partido por parte del cuerpo tecnico.',
                    ]);
                }

                $matches[] = $match;
            }
        }

        return $matches;
    }
}
