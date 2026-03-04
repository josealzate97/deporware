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
        <label for="player-tab-observations">Observaciones</label>

        <div class="player-tab-panels w-100">
            <div class="player-tab-panel" data-panel="general">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="fw-semibold">Jugador</div>
                        <div>{{ $player->name }} {{ $player->lastname }}</div>
                        <div class="text-muted small">NIT: {{ $player->nit ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="fw-semibold">Contacto</div>
                        <div>{{ $player->email ?? '-' }}</div>
                        <div>{{ $player->phone ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="fw-semibold">Estado</div>
                        @if($player->status == \App\Models\Player::ACTIVE)
                            <span class="status-pill status-pill-success">Activo</span>
                        @else
                            <span class="status-pill status-pill-muted">Inactivo</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="player-tab-panel" data-panel="contacts">
                @php($contact = $player->contacts->first())
                @if(!$contact)
                    <div class="text-muted">Sin contactos registrados.</div>
                @else
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fw-semibold">Contacto principal</div>
                            <div>{{ $contact->name }} {{ $contact->lastname }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold">Email / Teléfono</div>
                            <div>{{ $contact->email }}</div>
                            <div>{{ $contact->phone }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold">Dirección</div>
                            <div>{{ $contact->address ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold">Ciudad</div>
                            <div>{{ $contact->city ?? '-' }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="player-tab-panel" data-panel="observations">
                @if($player->observations->isEmpty())
                    <div class="text-muted">Sin observaciones registradas.</div>
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
