<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha de jugador</title>
    <style>
        :root {
            --brand-blue: #1b77d3;
            --brand-blue-dark: #0b4e91;
            --brand-blue-soft: #e8f1ff;
            --brand-green: #16a34a;
            --brand-green-soft: #e6f6ed;
            --ink: #0f172a;
            --muted: #6b7280;
            --line: #d7e4f6;
            --card: #f8fbff;
            --shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 24px;
            font-family: "Arial", sans-serif;
            color: var(--ink);
            background: #f5f7fb;
        }
        .sheet {
            max-width: 980px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            padding: 18px 22px 26px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            inset: 0;
            background: url("{{ asset('images/branding/logo_full.png') }}") center/60% no-repeat;
            opacity: 0.12;
            pointer-events: none;
            z-index: 0;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            margin-bottom: 18px;
        }
        .header-bar {
            display: grid;
            grid-template-columns: 200px 1fr;
            align-items: stretch;
            min-height: 90px;
            background: #0b4e91;
        }
        .header-logo {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 18px;
        }
        .logo {
            width: 140px;
            height: auto;
            display: block;
        }
        .title-bar {
            background: #0b4e91;
            color: #ffffff;
            font-weight: 800;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 24px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 1.25rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 14px;
            margin-bottom: 14px;
        }
        .field {
            display: grid;
            grid-template-columns: 32px 1fr;
            gap: 10px;
            align-items: center;
        }
        .icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #dbeafe;
            border: 1px solid #c7ddfb;
            display: grid;
            place-items: center;
            color: var(--brand-blue-dark);
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.08);
        }
        .icon svg {
            width: 16px;
            height: 16px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .field-body {
            background: var(--brand-blue-soft);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 6px 10px;
        }
        .field-label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }
        .field-value {
            min-height: 16px;
            font-size: 13px;
            font-weight: 600;
        }
        .chips-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 14px;
        }
        .chip-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
        .chip-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
        }
        .chips {
            display: inline-flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 999px;
            overflow: hidden;
        }
        .chip {
            border-right: 1px solid var(--line);
            background: #ffffff;
            color: var(--muted);
            font-size: 12px;
            padding: 4px 14px;
            font-weight: 700;
        }
        .chip:last-child {
            border-right: none;
        }
        .chip.is-active {
            background: var(--brand-blue);
            border-color: var(--brand-blue-dark);
            color: #ffffff;
        }
        .chip.is-active.is-green {
            background: var(--brand-green);
            border-color: var(--brand-green);
        }
        .cards {
            display: grid;
            gap: 12px;
        }
        .cards.full-width {
            grid-template-columns: 1fr;
        }
        .card {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--line);
            padding: 10px 12px 12px;
            box-shadow: 0 6px 12px rgba(15, 23, 42, 0.06);
        }
        .card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .card-title span {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 12px;
        }
        .card-title span svg {
            width: 13px;
            height: 13px;
            display: block;
            fill: currentColor;
        }
        .card-title strong {
            font-size: 13px;
        }
        .card-body {
            font-size: 12px;
            color: var(--muted);
            min-height: 48px;
        }
        .card-success span {
            background: var(--brand-green);
        }
        .card-danger span {
            background: #ef4444;
        }
        .card-warning span {
            background: #f59e0b;
        }
        .card-info span {
            background: #6366f1;
        }
        .card-note span {
            background: #fbbf24;
        }
        .card.full .card-body {
            min-height: 58px;
        }
        @media (max-width: 860px) {
            .header {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .logo {
                margin: 0 auto;
            }
            .info-grid,
            .chips-row,
            .cards.full-width {
                grid-template-columns: 1fr;
            }
        }
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
@endphp
    <div class="sheet">
        <div class="watermark" aria-hidden="true"></div>
        <div class="content">
            <div class="header">
                <div class="header-bar">
                    <div class="header-logo">
                        <img src="{{ asset('images/branding/logo_half.png') }}" alt="Deporware" class="logo">
                    </div>
                    <div class="title-bar">Ficha de jugador</div>
                </div>
            </div>

            <div class="info-grid">

                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div> 
                    <div class="field-body">
                        <div class="field-label">Nombre completo</div>
                        <div class="field-value">{{ $fullName }}</div>
                    </div>
                </div>

                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                    </div>
                    <div class="field-body">
                        <div class="field-label">Fecha de nacimiento</div>
                        <div class="field-value">{{ $birthdate }}</div>
                    </div>
                </div>

                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M8 3v3M16 3v3"/><path d="M3 8h18"/><rect x="3" y="6" width="18" height="15" rx="2"/><path d="M8 13h8"/></svg>
                    </div>
                    <div class="field-body">
                        <div class="field-label">Numero del jugador</div>
                        <div class="field-value">{{ $dorsal }}</div>
                    </div>
                </div>

                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 2l7 3v6c0 5-3.5 9.7-7 11-3.5-1.3-7-6-7-11V5l7-3z"/></svg>
                    </div>
                    <div class="field-body">
                        <div class="field-label">Demarcacion principal</div>
                        <div class="field-value">{{ $primaryPosition }}</div>
                    </div>
                </div>

                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
                    </div>
                    <div class="field-body">
                        <div class="field-label">Demarcacion secundaria</div>
                        <div class="field-value">{{ $secondaryPosition }}</div>
                    </div>
                </div>

                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 0 0 20M2 12h20"/></svg>
                    </div>
                    <div class="field-body">
                        <div class="field-label">Club de origen</div>
                        <div class="field-value"></div>
                    </div>
                </div>
                
                <div class="field">
                    <div class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M7 2v4M17 2v4M3 10h18"/></svg>
                    </div>
                    <div class="field-body">
                        <div class="field-label">Fecha de incorporacion</div>
                        <div class="field-value"></div>
                    </div>
                </div>
            </div>

            <div class="chips-row">
                <div class="chip-group">
                    <div class="chip-title">Lateralidad dominante</div>
                    <div class="chips">
                        <div class="chip {{ $footValue === \App\Models\Player::PIE_DERECHO ? 'is-active' : '' }}">Diestro</div>
                        <div class="chip {{ $footValue === \App\Models\Player::PIE_IZQUIERDO ? 'is-active' : '' }}">Zurdo</div>
                        <div class="chip {{ $footValue === \App\Models\Player::PIE_AMBOS ? 'is-active' : '' }}">Ambos</div>
                    </div>
                </div>
                <div class="chip-group">
                    <div class="chip-title">Mentalidad</div>
                    <div class="chips">
                        <div class="chip is-green {{ $mentalidad === 'ofensiva' ? 'is-active' : '' }}">Ofensiva</div>
                        <div class="chip {{ $mentalidad === 'defensiva' ? 'is-active' : '' }}">Defensiva</div>
                    </div>
                </div>
            </div>

            <div class="cards full-width">
                <div class="card card-success">
                    <div class="card-title">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M2 20h2c.55 0 1-.45 1-1v-9c0-.55-.45-1-1-1H2v11zm19.83-7.12c.11-.25.17-.52.17-.8V11c0-1.1-.9-2-2-2h-5.5l.92-4.65c.05-.22.02-.46-.08-.66-.23-.45-.52-.86-.88-1.22L14 2 7.59 8.41C7.21 8.79 7 9.3 7 9.83V19c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-6.9z"/></svg>
                        </span>
                        <strong>Puntos fuertes</strong>
                    </div>
                    <div class="card-body"></div>
                </div>
                <div class="card card-danger">
                    <div class="card-title">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        </span>
                        <strong>Puntos debiles</strong>
                    </div>
                    <div class="card-body"></div>
                </div>
            </div>

            <div class="cards" style="margin-top: 12px;">
                <div class="card card-warning full">
                    <div class="card-title">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </span>
                        <strong>Singularidades</strong>
                    </div>
                    <div class="card-body"></div>
                </div>
                <div class="card card-info full">
                    <div class="card-title">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 12c2.76 0 5-2.24 5-5S14.76 2 12 2 7 4.24 7 7s2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/></svg>
                        </span>
                        <strong>Caracter</strong>
                    </div>
                    <div class="card-body"></div>
                </div>
                <div class="card card-note full">
                    <div class="card-title">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14l4-4h12c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                        </span>
                        <strong>Observaciones</strong>
                    </div>
                    <div class="card-body"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
