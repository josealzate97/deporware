<div class="section-hero mb-3">
    <div class="d-flex align-items-start gap-3">
        <div class="section-hero-icon">
            <i class="fa-solid fa-people-group"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1">Información del Jugador</h3>
            <div class="text-muted small fw-bold">Consulta los datos principales del jugador</div>
        </div>
    </div>
</div>

<div class="card p-3 section-card">
    <div class="player-tabs">
        <input type="radio" id="player-tab-general" name="player-tabs" checked>
        <label for="player-tab-general">General</label>

        <input type="radio" id="player-tab-contacts" name="player-tabs">
        <label for="player-tab-contacts">Contactos</label>

        <input type="radio" id="player-tab-observations" name="player-tabs">
        <label for="player-tab-observations">Ficha Valorativa</label>

        <div class="player-tab-panels w-100">
            @php($nationalityOptions = \App\Models\Player::nationalityOptions())
            @php($positionOptions = \App\Models\Player::positionOptions())
            @php($footOptions = \App\Models\Player::footOptions())

            <div class="player-tab-panel" data-panel="general">
                <div class="player-info-grid">
                    <div class="player-info-item">
                        <div class="player-info-label">
                            <i class="fa-solid fa-user text-primary me-2"></i>
                            Jugador
                        </div>
                        <div class="player-info-value">{{ $player->name }} {{ $player->lastname }}</div>
                        <div class="player-info-sub">
                            NIT:
                            <span class="player-badge-blue">{{ $player->nit ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="player-info-item">
                        <div class="player-info-label">
                            <i class="fa-solid fa-phone text-primary me-2"></i>
                            Contacto
                        </div>
                        <div class="player-info-value">{{ $player->email ?? '-' }}</div>
                        <div class="player-info-sub">
                            <span class="player-badge-blue">{{ $player->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="player-info-item">
                        <div class="player-info-label">
                            <i class="fa-solid fa-cake-candles text-primary me-2"></i>
                            Nacimiento
                        </div>
                        <div class="player-info-value">
                            {{ $player->birthdate?->format('Y-m-d') ?? '-' }}
                            @if($player->birthdate)
                                <span class="player-badge-green">
                                    {{ \Carbon\Carbon::parse($player->birthdate)->age }} años
                                </span>
                            @endif
                        </div>
                        <div class="player-info-sub">
                            Nacionalidad:
                            <span class="player-badge-blue">{{ $nationalityOptions[$player->nacionality] ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="player-info-item">
                        <div class="player-info-label">
                            <i class="fa-solid fa-futbol text-primary me-2"></i>
                            Perfil deportivo
                        </div>
                        <div class="player-info-value">Posición: {{ $positionOptions[$player->position] ?? '-' }}</div>
                        <div class="player-info-sub">
                            Pierna hábil:
                            <span class="player-badge-blue">{{ $footOptions[$player->foot] ?? '-' }}</span>
                        </div>
                        <div class="player-info-sub">
                            Peso:
                            <span class="player-badge-blue">{{ $player->weight ?? '-' }} kg</span>
                        </div>
                    </div>
                    <div class="player-info-item">
                        <div class="player-info-label">
                            <i class="fa-solid fa-shirt text-primary me-2"></i>
                            Dorsal
                        </div>
                        <div class="player-info-value">{{ $player->dorsal ?? '-' }}</div>
                    </div>
                    <div class="player-info-item">
                        <div class="player-info-label">
                            <i class="fa-solid fa-toggle-on text-primary me-2"></i>
                            Estado
                        </div>
                        <div class="player-info-value">
                            @if($player->status == \App\Models\Player::ACTIVE)
                                <span class="status-pill status-pill-success">Activo</span>
                            @else
                                <span class="status-pill status-pill-muted">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="player-tab-panel" data-panel="contacts">
                @if($player->contacts->isEmpty())
                    <div class="text-muted">Sin contactos registrados.</div>
                @else
                    <div class="row g-2">
                        @foreach($player->contacts as $contact)
                            <div class="col-12 col-lg-6">
                                <div class="player-contact-card">
                                    <div class="player-contact-header">
                                        <span class="player-contact-icon">
                                            <i class="fa-solid fa-user-group"></i>
                                        </span>
                                        <div>
                                            <div class="fw-semibold">{{ $contact->name }} {{ $contact->lastname }}</div>
                                            <div class="player-contact-details">
                                                <span class="player-contact-email">{{ $contact->email }}</span>
                                                <span class="player-contact-phone">{{ $contact->phone }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="player-contact-meta">
                                        <div><i class="fa-solid fa-location-dot text-primary me-2"></i>{{ $contact->address ?? '-' }}</div>
                                        <div><i class="fa-solid fa-city text-primary me-2"></i>{{ $contact->city ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="player-tab-panel" data-panel="observations">
                @if($player->observations->isEmpty())
                    <div class="text-muted">Sin ficha valorativa registrada.</div>
                @else
                    <div class="row g-2">
                        @foreach($player->observations as $observation)
                            <div class="col-12 col-lg-6">
                                <div class="team-info-item">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $observationTypes[$observation->type] ?? 'Sin tipo' }}</div>
                                        <div class="text-muted small">{{ $observation->notes ?? '-' }}</div>
                                        <div class="text-muted small">
                                            {{ $observation->user?->name ?? 'Usuario' }} · {{ $observation->created_at?->format('Y-m-d') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
