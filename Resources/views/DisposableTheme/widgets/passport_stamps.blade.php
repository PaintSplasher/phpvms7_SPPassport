@if(isset($sppassport_css))
    <link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card mb-2">
            <div class="card-header p-1">
                <h5 class="m-1">@lang('sppassport::common.vcountries')</h5>
            </div>

            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-borderless table-striped align-middle text-nowrap mb-0">
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
                                                <img
                                                    src="{{ asset('sppassport/flags') }}/{{ strtolower($entry->country) }}.svg"
                                                    alt="{{ $entry->country }}"
                                                    class="rounded shadow-sm"
                                                    width="32"
                                                    height="24"
                                                >
                                                <div class="caption">
                                                    <span class="caption-text">
                                                        <a
                                                            href="{{ route('passport.index') }}"
                                                            class="text-decoration-none text-white"
                                                        >
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
            </div>

            <div class="card-footer p-0 px-1 small">
                @lang('sppassport::common.lcountries'):
                {{ strtoupper($last_stamp->country ?? '—') }}
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <div class="card text-center mb-2">
                    <div class="card-body p-2">{{ $travel_history->count() }}</div>
                    <div class="card-footer p-0 small fw-bold">
                        @lang('sppassport::common.visited_countries')
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card text-center mb-2">
                    <div class="card-body p-2">{{ $bestYear }}</div>
                    <div class="card-footer p-0 small fw-bold">
                        @lang('sppassport::common.best_year')
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card text-center mb-2">
                    <div class="card-body p-2">{{ $lastStampDate ?? '-' }}</div>
                    <div class="card-footer p-0 small fw-bold">
                        @lang('sppassport::common.last_new_stamp')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
