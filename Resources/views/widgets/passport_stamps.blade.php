@if(isset($sppassport_css))
    <link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

<div class="row">
    <div class="col">
        <div class="card my-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span>@lang('sppassport::common.vcountries')</span>
                <small>@lang('sppassport::common.lcountries'): <img src="{{ asset('sppassport/flags') }}/{{ strtolower($last_stamp->arr_airport->country ?? '-') }}.svg" alt="{{ $last_stamp->country ?? '-' }}" class="rounded shadow-sm" width="32" height="24"></small>
            </div>
            <div class="card-body">
                @if(isset($last_stamp))
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>@lang('sppassport::common.year')</th>
                            <th>@lang('sppassport::common.countries_visited')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $year => $entries)
                            <tr>
                                <td><strong>{{ $year }}</strong></td>
                                <td>
                                    <ul class="list-inline d-flex flex-wrap mb-0">
                                        @foreach($entries as $entry)
                                            <li class="list-inline-item passport-flag">
                                                <img src="{{ asset('sppassport/flags') }}/{{ strtolower($entry->country) }}.svg"
                                                     alt="{{ $entry->country }}"
                                                     class="rounded shadow-sm"
                                                     width="32" height="24">
                                                <div class="caption">
                                                    <span class="caption-text">
                                                        <a href="{{ route('passport.index') }}"
                                                           class="text-decoration-none text-white">
                                                           {{ strtoupper($entry->country) }}
                                                        </a>
                                                    </span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                <div class="row">
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="social-description">
                                    <h2 class="card-title">{{ $travel_history->count() ?? '-' }}</h2>
                                    <p class="card-text">@lang('sppassport::common.visited_countries')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="social-description">
                                    <h2 class="card-title">{{ $bestYear ?? '-' }}</h2>
                                    <p class="card-text">@lang('sppassport::common.best_year')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="social-description">
                                    <h2 class="card-title">{{ $lastStampDate ?? '-' }}</h2>
                                    <p class="card-text">@lang('sppassport::common.last_new_stamp')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
