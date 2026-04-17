<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha de jugador</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            color: #0f172a;
            background: #f5f7fb;
        }
        .sheet {
            max-width: 794px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            padding: 18px 22px 26px;
            position: relative;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-image: url("file://{{ public_path('images/branding/logo_full.png') }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: 60%;
            opacity: 0.12;
            z-index: 0;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            margin-bottom: 18px;
        }

        /* ── Header bar: tabla HTML ── */
        .header-bar {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo {
            width: 200px;
            background: #ffffff;
            text-align: center;
            vertical-align: middle;
            padding: 14px 18px;
        }
        .logo {
            width: 140px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .title-bar {
            background: #0b4e91;
            color: #ffffff;
            font-weight: 800;
            text-align: center;
            vertical-align: middle;
            padding: 20px 24px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 1.2rem;
        }

        /* ── Info grid: tabla HTML 2 col ── */
        .info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-bottom: 4px;
        }
        .info-td {
            width: 50%;
            vertical-align: middle;
        }
        .info-td-left {
            padding-right: 7px;
        }
        .info-td-right {
            padding-left: 7px;
        }

        /* ── Field: tabla HTML real ── */
        .field {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .field-icon-cell {
            width: 32px;
            vertical-align: middle;
            padding-right: 10px;
        }
        .icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #dbeafe;
            border: 1px solid #c7ddfb;
            text-align: center;
            line-height: 30px;
        }
        .icon-img {
            width: 16px;
            height: 16px;
            display: inline-block;
            margin: 0;
            vertical-align: middle;
        }
        .field-body-cell {
            vertical-align: top;
        }
        .field-body {
            background: #e8f1ff;
            border: 1px solid #d7e4f6;
            border-radius: 10px;
            padding: 6px 10px;
        }
        .field-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }
        .field-value {
            font-size: 13px;
            font-weight: 600;
        }

        /* ── Chips row: tabla HTML 2 col ── */
        .chips-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .chip-group-cell {
            width: 50%;
            vertical-align: top;
            padding-right: 14px;
        }
        .chip-group-cell-last {
            padding-right: 0;
        }
        .chip-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .chips-table {
            border-collapse: collapse;
            border: 1px solid #d7e4f6;
            border-radius: 8px;
        }
        .chip {
            border-right: 1px solid #d7e4f6;
            background: #ffffff;
            color: #6b7280;
            font-size: 12px;
            padding: 4px 14px;
            font-weight: 700;
            vertical-align: middle;
        }
        .chip-last {
            border-right: none;
        }
        .chip.is-active {
            background: #1b77d3;
            border-color: #0b4e91;
            color: #ffffff;
        }
        .chip.is-active.is-green {
            background: #16a34a;
            border-color: #16a34a;
            color: #ffffff;
        }

        /* ── Cards ── */
        .card {
            background: #f8fbff;
            border-radius: 12px;
            border: 1px solid #d7e4f6;
            padding: 10px 12px 12px;
            margin-bottom: 12px;
        }
        .card-title {
            display: table;
            width: auto;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 6px;
            font-weight: 700;
        }
        .card-title-icon {
            display: table-cell;
            width: 22px;
            height: 22px;
            border-radius: 7px;
            text-align: center;
            vertical-align: middle;
            color: #ffffff;
            padding: 0;
            line-height: 22px;
        }
        .card-title-icon svg {
            width: 13px;
            height: 13px;
            display: inline-block;
            margin: 0;
            vertical-align: middle;
        }
        .card-title-text {
            display: table-cell;
            vertical-align: middle;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }
        .card-body {
            font-size: 12px;
            color: #6b7280;
            min-height: 48px;
        }
        .observation-list {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        .observation-list li {
            margin-bottom: 6px;
            line-height: 1.35;
        }
        .observation-item {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .observation-item-icon-cell {
            display: table-cell;
            width: 18px;
            vertical-align: top;
            padding-right: 6px;
            padding-top: 1px;
        }
        .observation-item-icon {
            width: 13px;
            height: 13px;
            display: block;
        }
        .observation-item-text {
            display: table-cell;
            vertical-align: top;
            font-weight: 700;
            color: #334155;
        }
        .observation-empty {
            color: #94a3b8;
            font-style: italic;
        }
        .card.full .card-body {
            min-height: 58px;
        }
        .card-success .card-title-icon { background: #16a34a; }
        .card-danger  .card-title-icon { background: #ef4444; }
        .card-warning .card-title-icon { background: #f59e0b; }
        .card-info    .card-title-icon { background: #6366f1; }
        .card-note    .card-title-icon { background: #fbbf24; }
    </style>
</head>
<body>
@php
    $player = $player ?? null;
    $fullName = trim(($player->name ?? '') . ' ' . ($player->lastname ?? ''));
    $fullName = $fullName !== '' ? $fullName : 'Nombre completo';
    $dorsal = $player?->dorsal !== null ? (string) $player->dorsal : '';
    $birthdate = $player?->birthdate?->format('Y-m-d') ?? '';
    $positionLabels = $player?->position_labels ?? [];
    $primaryPosition = $positionLabels[0] ?? '';
    $secondaryPosition = $positionLabels[1] ?? '';
    $footValue = $player?->foot;
    $primaryPositionValue = $player?->primary_position ?? $player?->position;
    $defensivePositions = [
        \App\Models\Player::POSICION_ARQUERO,
        \App\Models\Player::POSICION_DEFENSA_CENTRAL,
        \App\Models\Player::POSICION_LATERAL_DERECHO,
        \App\Models\Player::POSICION_LATERAL_IZQUIERDO,
        \App\Models\Player::POSICION_MEDIOCAMPISTA_DEFENSIVO,
    ];
    $mentalidad = $player
        ? (in_array($primaryPositionValue, $defensivePositions, true) ? 'defensiva' : 'ofensiva')
        : null;
    $iconBasePath = public_path('images/templates');
    $iconAsset = static fn (string $icon): string => 'file://' . $iconBasePath . '/' . $icon . '.svg';
    $infoIcons = [
        'full_name' => $iconAsset('user'),
        'birthdate' => $iconAsset('calendar'),
        'dorsal' => $iconAsset('shirt-number'),
        'primary_position' => $iconAsset('shield'),
        'secondary_position' => $iconAsset('list'),
        'origin_club' => $iconAsset('globe'),
        'join_date' => $iconAsset('calendar-check'),
    ];
    $groupedObservationNotes = [
        \App\Models\PlayerObservation::TYPE_PSYCHIQUE => [],
        \App\Models\PlayerObservation::TYPE_TECHNICAL => [],
        \App\Models\PlayerObservation::TYPE_TACTIC => [],
        \App\Models\PlayerObservation::TYPE_PSYCOLOGICAL => [],
    ];
    $observationIcons = [
        \App\Models\PlayerObservation::TYPE_PSYCHIQUE => $iconAsset('observation-physical'),
        \App\Models\PlayerObservation::TYPE_TECHNICAL => $iconAsset('observation-technical'),
        \App\Models\PlayerObservation::TYPE_TACTIC => $iconAsset('observation-tactical'),
        \App\Models\PlayerObservation::TYPE_PSYCOLOGICAL => $iconAsset('observation-aptitudinal'),
    ];

    foreach (($player?->observations ?? collect()) as $observation) {
        $type = (int) ($observation->type ?? 0);
        $note = trim((string) ($observation->notes ?? ''));
        if ($note === '') {
            continue;
        }

        if (array_key_exists($type, $groupedObservationNotes)) {
            $groupedObservationNotes[$type][] = $note;
            continue;
        }

        $groupedObservationNotes[\App\Models\PlayerObservation::TYPE_PSYCOLOGICAL][] = $note;
    }
@endphp
    <div class="sheet">
        <div class="watermark" aria-hidden="true"></div>
        <div class="content">

            {{-- ── Header ── --}}
            <div class="header">
                <table class="header-bar" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="header-logo">
                            <img src="file://{{ public_path('images/branding/logo_half.png') }}" alt="Deporware" class="logo">
                        </td>
                        <td class="title-bar">Ficha de jugador</td>
                    </tr>
                </table>
            </div>

            {{-- ── Campos de información ── --}}
            <table class="info-grid" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="info-td info-td-left">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['full_name'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Nombre completo</div>
                                    <div class="field-value">{{ $fullName }}</div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                    <td class="info-td info-td-right">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['birthdate'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Fecha de nacimiento</div>
                                    <div class="field-value">{{ $birthdate }}</div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td class="info-td info-td-left">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['dorsal'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Numero del jugador</div>
                                    <div class="field-value">{{ $dorsal }}</div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                    <td class="info-td info-td-right">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['primary_position'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Demarcacion principal</div>
                                    <div class="field-value">{{ $primaryPosition }}</div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td class="info-td info-td-left">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['secondary_position'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Demarcacion secundaria</div>
                                    <div class="field-value">{{ $secondaryPosition }}</div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                    <td class="info-td info-td-right">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['origin_club'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Club de origen</div>
                                    <div class="field-value"></div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td class="info-td info-td-left">
                        <table class="field" cellspacing="0" cellpadding="0"><tr>
                            <td class="field-icon-cell">
                                <div class="icon">
                                    <img src="{{ $infoIcons['join_date'] }}" alt="" class="icon-img">
                                </div>
                            </td>
                            <td class="field-body-cell">
                                <div class="field-body">
                                    <div class="field-label">Fecha de incorporacion</div>
                                    <div class="field-value"></div>
                                </div>
                            </td>
                        </tr></table>
                    </td>
                    <td class="info-td info-td-right"></td>
                </tr>
            </table>

            {{-- ── Chips ── --}}
            <table class="chips-row-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="chip-group-cell">
                        <div class="chip-title">Lateralidad dominante</div>
                        <table class="chips-table" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="chip {{ $footValue === \App\Models\Player::PIE_DERECHO ? 'is-active' : '' }}">Diestro</td>
                                <td class="chip {{ $footValue === \App\Models\Player::PIE_IZQUIERDO ? 'is-active' : '' }}">Zurdo</td>
                                <td class="chip chip-last {{ $footValue === \App\Models\Player::PIE_AMBOS ? 'is-active' : '' }}">Ambos</td>
                            </tr>
                        </table>
                    </td>
                    <td class="chip-group-cell chip-group-cell-last">
                        <div class="chip-title">Mentalidad</div>
                        <table class="chips-table" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="chip is-green {{ $mentalidad === 'ofensiva' ? 'is-active' : '' }}">Ofensiva</td>
                                <td class="chip chip-last {{ $mentalidad === 'defensiva' ? 'is-active' : '' }}">Defensiva</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- ── Cards ── --}}
            <div class="card card-success">
                <div class="card-title">
                    <div class="card-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="#ffffff"><path d="M2 20h2c.55 0 1-.45 1-1v-9c0-.55-.45-1-1-1H2v11zm19.83-7.12c.11-.25.17-.52.17-.8V11c0-1.1-.9-2-2-2h-5.5l.92-4.65c.05-.22.02-.46-.08-.66-.23-.45-.52-.86-.88-1.22L14 2 7.59 8.41C7.21 8.79 7 9.3 7 9.83V19c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-6.9z"/></svg>
                    </div>
                    <div class="card-title-text">Observacion Fisica</div>
                </div>
                <div class="card-body">
                    @if(!empty($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_PSYCHIQUE]))
                        <ul class="observation-list">
                            @foreach($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_PSYCHIQUE] as $note)
                                <li>
                                    <div class="observation-item">
                                        <span class="observation-item-icon-cell">
                                            <img src="{{ $observationIcons[\App\Models\PlayerObservation::TYPE_PSYCHIQUE] }}" alt="" class="observation-item-icon">
                                        </span>
                                        <span class="observation-item-text">{{ $note }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="observation-empty">Sin observaciones registradas.</div>
                    @endif
                </div>
            </div>

            <div class="card card-danger">
                <div class="card-title">
                    <div class="card-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="#ffffff"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <div class="card-title-text">Observacion Tecnica</div>
                </div>
                <div class="card-body">
                    @if(!empty($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_TECHNICAL]))
                        <ul class="observation-list">
                            @foreach($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_TECHNICAL] as $note)
                                <li>
                                    <div class="observation-item">
                                        <span class="observation-item-icon-cell">
                                            <img src="{{ $observationIcons[\App\Models\PlayerObservation::TYPE_TECHNICAL] }}" alt="" class="observation-item-icon">
                                        </span>
                                        <span class="observation-item-text">{{ $note }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="observation-empty">Sin observaciones registradas.</div>
                    @endif
                </div>
            </div>

            <div class="card card-warning full">
                <div class="card-title">
                    <div class="card-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="#ffffff"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    </div>
                    <div class="card-title-text">Observacion Tactica & Conceptual</div>
                </div>
                <div class="card-body">
                    @if(!empty($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_TACTIC]))
                        <ul class="observation-list">
                            @foreach($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_TACTIC] as $note)
                                <li>
                                    <div class="observation-item">
                                        <span class="observation-item-icon-cell">
                                            <img src="{{ $observationIcons[\App\Models\PlayerObservation::TYPE_TACTIC] }}" alt="" class="observation-item-icon">
                                        </span>
                                        <span class="observation-item-text">{{ $note }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="observation-empty">Sin observaciones registradas.</div>
                    @endif
                </div>
            </div>

            <div class="card card-info full">
                <div class="card-title">
                    <div class="card-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="#ffffff"><path d="M12 12c2.76 0 5-2.24 5-5S14.76 2 12 2 7 4.24 7 7s2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/></svg>
                    </div>
                    <div class="card-title-text">Observacion Aptitudinal</div>
                </div>
                <div class="card-body">
                    @if(!empty($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_PSYCOLOGICAL]))
                        <ul class="observation-list">
                            @foreach($groupedObservationNotes[\App\Models\PlayerObservation::TYPE_PSYCOLOGICAL] as $note)
                                <li>
                                    <div class="observation-item">
                                        <span class="observation-item-icon-cell">
                                            <img src="{{ $observationIcons[\App\Models\PlayerObservation::TYPE_PSYCOLOGICAL] }}" alt="" class="observation-item-icon">
                                        </span>
                                        <span class="observation-item-text">{{ $note }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="observation-empty">Sin observaciones registradas.</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</body>
</html>
