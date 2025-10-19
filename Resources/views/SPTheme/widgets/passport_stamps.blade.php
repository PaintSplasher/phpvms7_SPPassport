@if(isset($sppassport_css))
    <link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

<div class="row">
    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border mb-0">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom">
                    <i class="ph-fill ph-identification-card align-middle fs-20 me-1"></i>
                    @lang('sppassport::common.vcountries')
                    <span class="float-end fw-normal small">
                        @lang('sppassport::common.lcountries'):
                        <img src="{{ asset('sppassport/flags') }}/{{ strtolower($last_stamp->country ?? '—') }}.svg"
                                                     alt="{{ strtoupper($last_stamp->country ?? '—') }}"
                                                     class="rounded shadow-sm"
                                                     width="32" height="24">
                        
                    </span>
                </h4>

                <table class="table table-striped table-hover table-responsive">
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
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
            <div class="card border">
                <div class="card-body widget-desk">
                    <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold">{{ $travel_history->count() }}</h4>
                        <p class="mb-0">@lang('sppassport::common.visited_countries')</p>
                    </div>
                    <div class="widget-icon">
                        <i class="ph-fill ph-globe-simple-x"></i>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
            <div class="card border">
                <div class="card-body widget-desk">
                    <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold">{{ $bestYear }}</h4>
                        <p class="mb-0">@lang('sppassport::common.best_year')</p>
                    </div>
                    <div class="widget-icon">
                        <i class="ph-fill ph-clock-user"></i>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
            <div class="card border">
                <div class="card-body widget-desk">
                    <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold">{{ $lastStampDate ?? '-' }}</h4>
                        <p class="mb-0">@lang('sppassport::common.last_new_stamp')</p>
                    </div>
                    <div class="widget-icon">
                        <i class="ph-fill ph-info"></i>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
