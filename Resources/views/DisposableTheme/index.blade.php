@extends('sppassport::layouts.frontend')
@section('title', __('sppassport::common.title'))
@section('content')

@if(isset($sppassport_css))
<link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card mb-2 p-2">
        <div class="input-group input-group-sm mt-1">
            <span class="input-group-text col-lg-4">@lang('sppassport::common.compare_with')</span>
            <select class="form-select select2" name="user" id="compare-user" style="z-index:2">
                <option value="">@lang('sppassport::common.select_pilot')</option>
                @foreach($users as $u)
                    @if($u->id !== auth()->id())
                        <option value="{{ $u->id }}">{{ $u->name_private }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        </div>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-4">
    @include('sppassport::stats')
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.visited_countries')</h5>
            </div>
            <div class="card-body p-0">
                @include('sppassport::map')           
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.visited_countries')</h5>
            </div>
            <div class="card-body">
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success text-black" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $visitedCount }} / {{ $totalCountries }}
                    </div>
                </div>
                <div class="text-center mt-4">
                    <ul class="list-inline d-flex flex-wrap justify-content-center gap-2 mb-0">
                        @foreach ($allCountries as $iso)
                            @php $visited = in_array($iso, array_keys($countryData)); @endphp
                            <li class="list-inline-item passport-flag">
                                <img data-iso="{{ $iso }}" src="{{ asset('sppassport/flags') }}/{{ strtolower($iso) }}.svg"
                                     alt="{{ $iso }}" class="rounded shadow-sm {{ $visited ? 'passport-on' : 'passport-off' }}"
                                     width="64" height="48">
                                <div class="caption">
                                    <span class="caption-text">{{ $iso }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($recommendations))
<div class="row">
    <div class="col">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.recommended_destinations')</h5>
            </div>
            <div class="card-body">
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5 mb-0">
                    @foreach($recommendations as $country)
                        <li class="text-center">
                            <img src="{{ asset('sppassport/flags/' . strtolower($country) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold">{{ strtoupper($country) }}</div>
                            <a href="{{ route('passport.flights.country', ['country' => strtoupper($country)]) }}">
                                @lang('sppassport::common.search')
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.recommendation_intro')
            </div>
        </div>
    </div>
    @if(isset($rival))
    <div class="col">
        <div class="card mb-2">
            <div class="card-header bg-warning p-1">
                <h5 class="m-1 text-black">@lang('sppassport::common.rival_of_the_week')</h5>
            </div>
            <div class="card-body text-center">
                <ul class="list-inline d-flex flex-wrap justify-content-center mb-0">
                    <li><img src="{{ asset('sppassport/flags/' . strtolower($rival->user_country) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                    <div class="fw-bold">{{ strtoupper($rival->user_country) }}</div>
                    <a href="{{ route('frontend.users.show.public', [$rival->user_id]) }}">{{ $rival->user_name }}</a>
                </li>
            </ul>
            </div>
            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.countries_to_overtake', ['count' => max(1, ($rival->countries ?? 0) - ($visitedCount ?? 0))])
            </div>
        </div>
    </div>
    @endif
    <div class="col">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.top_visited_countries')</h5>
            </div>
            <div class="card-body">
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5 mb-0">
                    @foreach ($topCountries as $country)
                        <li class="text-center">
                            <img src="{{ asset('sppassport/flags/' . strtolower($country['country']) . '.svg') }}"
                                 width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold">{{ strtoupper($country['country']) }}</div>
                            <div>{{ $country['flights'] }} @lang('sppassport::common.flights')</div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.you_have_visited', ['visited' => $visitedCount, 'total' => $totalCountries])
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($rareAirports))
<div class="row">
    <div class="col-md-12">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.rare_destinations')</h5>
            </div>
            <div class="card-body">
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5 mb-0">
                    @foreach($rareAirports as $airport)
                        <li class="text-center">
                            <img src="{{ asset('sppassport/flags/' . strtolower($airport->country) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold">{{ strtoupper($airport->country) }}</div>
                            <a href="{{ route('frontend.airports.show', [$airport->icao]) }}">
                                {{ $airport->icao }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.least_flown_destinations')
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($weeklyTop) && $weeklyTop->isNotEmpty())
<div class="row">
    <div class="col-md-12">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.rising_stars')</h5>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-borderless table-striped align-middle text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>@lang('sppassport::common.rank')</th>
                            <th>@lang('sppassport::common.pilot')</th>
                            <th class="text-center">@lang('sppassport::common.flights')</th>
                            <th class="text-center">@lang('sppassport::common.flight_time')</th>
                            <th class="text-center">@lang('sppassport::common.distance')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklyTop as $pilot)
                            <tr>
                                <td>{{ $loop->iteration }}.</td>
                                <td>
                                    <a href="{{ route('frontend.users.show.public', [$pilot->id]) }}">
                                        {{ $pilot->name }}
                                    </a>
                                    <span class="fi fi-{{ strtolower($pilot->country) }}"></span>
                                </td>
                                <td class="text-center">{{ $pilot->flights }} @lang('sppassport::common.new_flights')</td>
                                <td class="text-center">@minutestotime($pilot->flight_time)</td>
                                <td class="text-center">{{ $pilot->distance->local(0).' '.setting('units.distance') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.weekly_top_pilots')
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($leaderboard))
<div class="row">
    <div class="col-md-12">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.world_leaderboard')</h5>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-borderless table-striped align-middle text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>@lang('sppassport::common.rank')</th>
                            <th>@lang('sppassport::common.pilot')</th>
                            <th class="text-center">@lang('sppassport::common.countries')</th>
                            <th class="text-center">@lang('sppassport::common.flights')</th>
                            <th class="text-center">@lang('sppassport::common.flight_time')</th>
                            <th class="text-center">@lang('sppassport::common.distance')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $index => $entry)
                            <tr>
                                <td>{{ $loop->iteration }}.</td>
                                <td>
                                    <a href="{{ route('frontend.users.show.public', [$entry->user_id]) }}">
                                        {{ $entry->user_name }}
                                    </a>
                                    <span class="fi fi-{{ strtolower($entry->user_country) }}"></span>
                                </td>
                                <td class="text-center">{{ $entry->countries }}</td>
                                <td class="text-center">{{ $entry->flights }}</td>
                                <td class="text-center">@minutestotime($entry->flight_time)</td>
                                <td class="text-center">{{ $entry->distance->local(0).' '.setting('units.distance') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.top_pilots')
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#compare-user').select2({
        placeholder: "@lang('flights.search')",
        allowClear: true,
        width: '100%'
    });

    $('#compare-user').on('change', function() {
        const value = $(this).val();
        if (value) {
            window.location = '/passport/compare/' + value;
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const countryData = @json($countryData ?? []);
    const waitForMapAndMarkers = (callback) => {
        const interval = setInterval(() => {
            if (window.sppassportMap && window.airportMarkers) {
                clearInterval(interval);
                callback(window.sppassportMap, window.airportMarkers);
            }
        }, 300);
    };

    waitForMapAndMarkers((sppassportMap, airportMarkers) => {
        const countryLayers = window.countryLayers || {};
        document.addEventListener('click', (event) => {
            const flagElement = event.target.closest('.passport-flag');
            if (!flagElement) return;

            const iso = flagElement.querySelector('img')?.dataset.iso?.toUpperCase();
            if (!iso) return;

            const countryInfo = countryData[iso];
            if (!countryInfo?.first_airport) return;

            const marker = airportMarkers[countryInfo.first_airport.toUpperCase()];
            const layer = countryLayers[iso];
            if (!marker || !layer) return;

            sppassportMap.flyTo(marker.getLatLng(), 6, { duration: 1.2 });

            sppassportMap.once('moveend', () => {
                const popup = layer.getPopup();
                if (popup) {
                    popup.setLatLng(marker.getLatLng());
                    sppassportMap.openPopup(popup);
                    layer.setStyle({ weight: 3, color: '#1abc9c', fillOpacity: 0.7 });
                    setTimeout(() => {
                        layer.setStyle({ weight: 1, color: '#2ecc71', fillOpacity: 0.5 });
                    }, 2000);
                }
            });
        });
    });
});
</script>
@endsection
