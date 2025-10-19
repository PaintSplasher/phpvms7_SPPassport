    <div class="col">
        <div class="card text-center mb-2">
            <div class="card-body p-2">{{ $progress }}%</div>
            <div class="card-footer p-0 small fw-bold">@lang('sppassport::common.world_coverage')</div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center mb-2">
            <div class="card-body p-2">@minutestotime($totalFlightMinutes)</div>
            <div class="card-footer p-0 small fw-bold">@lang('sppassport::common.flight_time')</div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center mb-2">
            <div class="card-body p-2">{{ number_format($totalDistance, 0, ',', '.') }} {{ setting('units.distance') }}</div>
            <div class="card-footer p-0 small fw-bold">@lang('sppassport::common.total_distance')</div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center mb-2">
            <div class="card-body p-2">{{ $uniqueAirports }}</div>
            <div class="card-footer p-0 small fw-bold">@lang('sppassport::common.airports_visited')</div>
        </div>
    </div>