<div class="col-md-3 mb-4">
    <div class="card text-center">
        <div class="card-body">
            <div class="social-description">
                <h2 class="card-title">{{ $progress }}%</h2>
                <p class="card-text">@lang('sppassport::common.world_coverage')</p>
            </div>
        </div>
    </div>
</div>
<div class="col-md-3 mb-4">
    <div class="card text-center">
        <div class="card-body">
            <div class="social-description">
                <h2 class="card-title">@minutestotime($totalFlightMinutes)</h2>
                <p class="card-text">@lang('sppassport::common.flight_time')</p>
            </div>
        </div>
    </div>
</div>
<div class="col-md-3 mb-4">
    <div class="card text-center">
        <div class="card-body">
            <div class="social-description">
                <h2 class="card-title">{{ $totalDistance->local(0).' '.setting('units.distance') }}</h2>
                <p class="card-text">@lang('sppassport::common.total_distance')</p>
            </div>
        </div>
    </div>
</div>
<div class="col-md-3 mb-4">
    <div class="card text-center">
        <div class="card-body">
            <div class="social-description">
                <h2 class="card-title">{{ $uniqueAirports }}</h2>
                <p class="card-text">@lang('sppassport::common.airports_visited')</p>
            </div>
        </div>
    </div>
</div>