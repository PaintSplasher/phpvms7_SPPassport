@extends('sppassport::layouts.frontend')
@section('title', __('sppassport::common.title'))
@section('content')

@if(isset($sppassport_css))
<link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

@php
    $rankImages = [
        asset('/SPTheme/images/placed_1.png'),
        asset('/SPTheme/images/placed_2.png'),
        asset('/SPTheme/images/placed_3.png'),
        asset('/SPTheme/images/placed_4.png'),
        asset('/SPTheme/images/placed_5.png'),
    ];
@endphp

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-trophy align-middle fs-20 me-1"></i>@lang('sppassport::common.compare_with')</h4>
                    <div class="form-group form-bg-grey rounded mb-3">
                        <div class="row">
                        <label class="col-1 control-label"><i class="ph-fill ph-info align-middle fs-20 me-1"></i></label>
                        <div class="col-11">
                            <div class="input-group input-group-lg">
                                <select name="user" id="compare-user" class="form-select">
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
            </div>   
        </div>
    </div>   
</div>

<div class="row">
    @include('sppassport::stats')
</div>

<div class="row">
    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                @include('sppassport::map')
            </div>
        </div>
    </div>
</div>

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-map-trifold align-middle fs-20 me-1"></i>@lang('sppassport::common.visited_countries')</h4>
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
    <div class="col">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i>@lang('sppassport::common.recommended_destinations')</h4>
                <p>@lang('sppassport::common.recommendation_intro')</p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    @foreach($recommendations as $country)
                        <li class="text-center">
                            <img src="{{ asset('sppassport/flags/' . strtolower($country) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold">{{ strtoupper($country) }}</div>
                            <a href="{{ route('passport.flights.country', ['country' => strtoupper($country)]) }}" class="tooltiptop" title="@lang('sppassport::common.flights')">
                                @lang('sppassport::common.search')
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @if(isset($rival))
    <div class="col">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-star align-middle fs-20 me-1"></i>@lang('sppassport::common.rival_of_the_week')</h4>
                <p>@lang('sppassport::common.countries_to_overtake', ['count' => max(1, ($rival->countries ?? 0) - ($visitedCount ?? 0))])</p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    <li class="text-center"><img src="{{ asset('sppassport/flags/' . strtolower($rival->user_country) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                    <div class="fw-bold">{{ strtoupper($rival->user_country) }}</div>
                    <a href="{{ route('frontend.users.show.public', [$rival->user_id]) }}">{{ $rival->user_name }}</a>
                </ul>
            </div>
        </div>
    </div>
    @endif
    <div class="col">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-flag align-middle fs-20 me-1"></i>@lang('sppassport::common.top_visited_countries')</h4>
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

@if(isset($rareAirports))
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i>@lang('sppassport::common.rare_destinations')</h4>
                <p>@lang('sppassport::common.least_flown_destinations')</p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    @foreach($rareAirports as $airport)
                        <li class="text-center">
                            <img src="{{ asset('sppassport/flags/' . strtolower($airport->country) . '.svg') }}" width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold">{{ strtoupper($airport->country) }}</div>
                            <a href="{{ route('frontend.airports.show', [$airport->icao]) }}" class="tooltiptop" title="{{ $airport->icao }}">
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
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-medal-military align-middle fs-20 me-1"></i>@lang('sppassport::common.rising_stars')</h4>
                <table class="table table-striped table-hover  table-responsive">
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
                            <td>
                                @if($loop->iteration <= 5)
                                    <img src="{{ $rankImages[$loop->iteration - 1] }}" alt="{{ $loop->iteration }}. Platz">
                                @else
                                    {{ $loop->iteration }}.
                                @endif
                            </td>
                            <td>
                                <span class="fi fi-{{ strtolower($pilot->country) }} shadow-img me-1" title="{{ strtolower($pilot->country) }}"></span>
                                <a href="{{ route('frontend.users.show.public', [$pilot->id]) }}" class="tooltiptop" title="{{ $loop->iteration }}.">{{ $pilot->name }}</a>
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
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-medal-military align-middle fs-20 me-1"></i>@lang('sppassport::common.world_leaderboard')</h4>
                <table class="table table-hover table-striped mb-0">
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
                    @foreach($leaderboard as $entry)
                        <tr>
                            <td>
                                @if($loop->iteration <= 5)
                                    <img src="{{ $rankImages[$loop->iteration - 1] }}" alt="{{ $loop->iteration }}. Platz">
                                @else
                                    {{ $loop->iteration }}.
                                @endif
                            </td>
                            <td>
                                <span class="fi fi-{{ $entry->user_country }} shadow-img me-1" title="{{ $entry->user_country }}"></span>
                                <a href="{{ route('frontend.users.show.public', [$entry->user_id]) }}" class="tooltiptop" title="{{ $loop->iteration }}.">{{ $entry->user_name }}</a>
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
