@extends('sppassport::layouts.frontend')
@section('title', __('sppassport::common.title'))
@section('content')

@if(isset($sppassport_css))
<link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

<div class="row">
    <div class="col-md-12">
        <div class="box-body">
            <form action="{{ url('passport/compare') }}" method="get">
                <div class="mb-3">
                    <label for="user" class="form-label">@lang('sppassport::common.compare_with')</label>
                    <select name="user" id="compare-user" class="form-select form-select-sm">
                        <option value="">@lang('sppassport::common.select_pilot')</option>
                        @foreach($users as $u)
                            @if($u->id !== auth()->id())
                                <option value="{{ $u->id }}">{{ $u->name_private }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    @include('sppassport::stats')
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box-body">
            @include('sppassport::map')
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">@lang('sppassport::common.visited_countries')</div>
            <div class="card-body">
                <p class="fw-bold text-center mb-0"></p>
                <div class="progress" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="height: 22px;">
                    <div class="progress-bar bg-primary" style="width: {{ $progress }}%">
                        {{ $visitedCount }} / {{ $totalCountries }}
                    </div>
                </div>
                <div class="text-center mt-4">
                    <ul class="list-inline d-flex flex-wrap justify-content-center gap-2">
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
    <div class="col-md-6">
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">@lang('sppassport::common.recommended_destinations')</div>
            <div class="card-body">
                <p>@lang('sppassport::common.recommendation_intro')</p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
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
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">@lang('sppassport::common.top_visited_countries')</div>
            <div class="card-body">
                <p>
                    @lang('sppassport::common.you_have_visited', [
                        'visited' => $visitedCount,
                        'total' => $totalCountries
                    ])
                </p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    @foreach ($topCountries as $country)
                        <li class="text-center">
                            <img src="{{ asset('sppassport/flags/' . strtolower($country['country']) . '.svg') }}"
                                 width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold">{{ strtoupper($country['country']) }}</div>
                            <p class="mb-0">{{ $country['flights'] }} @lang('sppassport::common.flights')</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($rival))
<div class="col-md-12">
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">@lang('sppassport::common.rival_of_the_week')</div>
        <div class="card-body">
            <p>@lang('sppassport::common.countries_to_overtake', ['count' => max(1, $rival['countries'] - $visitedCount)])</p>
            <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                <li class="text-center">
                    <img src="{{ asset('sppassport/flags/' . strtolower($rival['user_country']) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                    <div class="fw-bold">{{ strtoupper($rival['user_country']) }}</div>
                    <a href="{{ route('frontend.users.show.public', [$rival['user_id']]) }}">
                    {{ $rival['user_name'] }}
                </a>
                </li>
            </ul>
        </div>
    </div>
</div>
@endif

@if(isset($rareAirports))
<div class="row">
    <div class="col-md-12">
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">@lang('sppassport::common.rare_destinations')</div>
            <div class="card-body">
                <p>@lang('sppassport::common.least_flown_destinations')</p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
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
        </div>
    </div>
</div>
@endif

@if(isset($weeklyTop) && $weeklyTop->isNotEmpty())
<div class="row">
    <div class="col-md-12">
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                @lang('sppassport::common.rising_stars')
                <span class="small text-white float-end">@lang('sppassport::common.weekly_top_pilots')</span>
            </div>
            <div class="card-body">
                <table class="table table-responsive">
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
        </div>
    </div>
</div>
@endif

@if(isset($leaderboard))
<div class="row">
    <div class="col-md-12">
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                @lang('sppassport::common.world_leaderboard')
                <span class="small text-white float-end">@lang('sppassport::common.top_pilots')</span>
            </div>
            <div class="card-body">
                <table class="table table-responsive">
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
                                    <a href="{{ route('frontend.users.show.public', [$entry['user_id']]) }}">
                                        {{ $entry['user_name'] }}
                                    </a>
                                    <span class="fi fi-{{ $entry['user_country'] }}"></span>
                                </td>
                                <td class="text-center">{{ $entry['countries'] }}</td>
                                <td class="text-center">{{ $entry['flights'] }}</td>
                                <td class="text-center">@minutestotime($entry['flight_time'])</td>
                                <td class="text-center">{{ $entry['distance']->local(0).' '.setting('units.distance') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
    const compareSelect = new TomSelect('#compare-user', {
        placeholder: "@lang('flights.search')",
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        onChange(value) {
            if (value) {
                window.location = '/passport/compare/' + value;
            }
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
