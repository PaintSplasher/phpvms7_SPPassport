@extends('sppassport::layouts.frontend')

@section('title', __('sppassport::common.compare_with') . ' ' . $user->name)

@section('content')
    @if(isset($sppassport_css))
        <link rel="stylesheet" href="{{ $sppassport_css }}">
    @endif

    @php
        function cmp_icon($diff)
        {
            if ($diff > 0) {
                return '<i class="fas fa-thumbs-up text-success float-end"></i>';
            } elseif ($diff < 0) {
                return '<i class="fas fa-thumbs-down text-danger float-end"></i>';
            }
            return '<i class="fas fa-circle text-muted float-end"></i>';
        }

        $allContinents = array_unique(array_merge(
            array_keys($continentCoverageMine ?? []),
            array_keys($continentCoverageTheirs ?? [])
        ));
        sort($allContinents);
    @endphp
    <div class="row">
        <div class="col">
            <div class="card mb-2">
                <div class="card-header p-1">
                    <h5 class="m-1">{{ $current->name }}</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-borderless table-striped align-middle text-nowrap">
                        <tbody>
                            <tr>
                                <td>@lang('sppassport::common.similarity_score')</td>
                                <td>
                                    {{ $similarity }}%
                                    {!! cmp_icon($similarity - $similarity) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.country_count')</td>
                                <td>
                                    {{ $myCount }}
                                    {!! cmp_icon($myCount - $theirCount) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.continents_visited')</td>
                                <td>
                                    {{ $myContinents }}
                                    {!! cmp_icon($myContinents - $theirContinents) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.world_coverage')</td>
                                <td>
                                    {{ $worldCoverageMine }}%
                                    {!! cmp_icon($worldCoverageMine - $worldCoverageTheirs) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.longest_streak')</td>
                                <td>
                                    {{ $longestStreakMine }}
                                    {!! cmp_icon($longestStreakMine - $longestStreakTheirs) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.most_recent_country')</td>
                                <td>{{ $mostRecentMine }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <ul class="list-inline d-flex flex-wrap justify-content-center gap-2">
                        @foreach($onlyMine as $c)
                            <li class="list-inline-item passport-flag">
                                <img src="{{ asset('sppassport/flags') }}/{{ strtolower($c) }}.svg"
                                     alt="{{ $c }}"
                                     class="rounded shadow-sm"
                                     width="64"
                                     height="48">
                                <div class="caption">
                                    <span class="caption-text">{{ $c }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-2 d-flex justify-content-center align-items-center">
            <h2 class="fw-bold mb-0">@lang('sppassport::common.vs')</h2>
        </div>

        <div class="col">
            <div class="card mb-2">
                <div class="card-header p-1">
                    <h5 class="m-1">{{ $user->name }}</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-borderless table-striped align-middle text-nowrap">
                        <tbody>
                            <tr>
                                <td>@lang('sppassport::common.similarity_score')</td>
                                <td>
                                    {{ $similarity }}%
                                    {!! cmp_icon($similarity - $similarity) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.country_count')</td>
                                <td>
                                    {{ $theirCount }}
                                    {!! cmp_icon($theirCount - $myCount) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.continents_visited')</td>
                                <td>
                                    {{ $theirContinents }}
                                    {!! cmp_icon($theirContinents - $myContinents) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.world_coverage')</td>
                                <td>
                                    {{ $worldCoverageTheirs }}%
                                    {!! cmp_icon($worldCoverageTheirs - $worldCoverageMine) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.longest_streak')</td>
                                <td>
                                    {{ $longestStreakTheirs }}
                                    {!! cmp_icon($longestStreakTheirs - $longestStreakMine) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.most_recent_country')</td>
                                <td>{{ $mostRecentTheirs }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <ul class="list-inline d-flex flex-wrap justify-content-center gap-2">
                        @foreach($onlyTheirs as $c)
                            <li class="list-inline-item passport-flag">
                                <img src="{{ asset('sppassport/flags') }}/{{ strtolower($c) }}.svg"
                                     alt="{{ $c }}"
                                     class="rounded shadow-sm"
                                     width="64"
                                     height="48">
                                <div class="caption">
                                    <span class="caption-text">{{ $c }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-2">
                <div class="card-header p-1">
                    <h5 class="m-1">@lang('sppassport::common.advanced_comparisons')</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-borderless table-striped align-middle text-nowrap">
                        <tbody>
                            <tr>
                                <td>@lang('sppassport::common.shared_continents')</td>
                                <td>
                                    {{ $continentOverlap }}
                                    {!! cmp_icon($continentOverlap - 2) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.shared_countries')</td>
                                <td>
                                    {{ $sharedCount }}
                                    {!! cmp_icon($sharedCount - 10) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.country_overlap')</td>
                                <td>
                                    {{ $overlapPercentage }}%
                                    {!! cmp_icon($overlapPercentage - 50) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.unique_advantage')</td>
                                <td>
                                    +{{ $uniqueAdvantage }} @lang('sppassport::common.countries_visited')
                                    {!! cmp_icon($uniqueAdvantage) !!}
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.first_to_visit')</td>
                                <td>
                                    @if($firstToVisit === 'You')
                                        <i class="bi bi-hand-thumbs-up-fill text-success float-end"></i>
                                        @lang('sppassport::common.you')
                                    @elseif($firstToVisit === 'Them')
                                        <i class="bi bi-hand-thumbs-down-fill text-danger float-end"></i>
                                        @lang('sppassport::common.them')
                                    @else
                                        <i class="bi bi-circle-fill text-primary float-end"></i>
                                        @lang('sppassport::common.same_time')
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('sppassport::common.continent_coverage')</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($allContinents as $cont)
                                            @php
                                                $mine  = $continentCoverageMine[$cont]  ?? 0;
                                                $their = $continentCoverageTheirs[$cont] ?? 0;
                                                $diff  = $mine - $their;
                                            @endphp
                                            <li>
                                                <span class="fw-semibold">{{ $cont }}:</span>
                                                <span>
                                                    {{ $mine }}
                                                    <span class="mx-2">vs.</span>
                                                    {{ $their }}
                                                    {!! cmp_icon($diff) !!}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                            <tr class="fw-bold text-center">
                                <td>@lang('sppassport::common.win_counter')</td>
                                <td>
                                    @if($winner === 'Tie')
                                        <i class="fas fa-circle text-primary"></i>
                                        @lang('sppassport::common.tie')
                                    @elseif($winner === 'You')
                                        <i class="fas fa-thumbs-up text-success"></i>
                                        @lang('sppassport::common.you_win')
                                    @else
                                        <i class="fas fa-thumbs-down text-danger"></i>
                                        @lang('sppassport::common.they_win', ['name' => $user->name])
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
