@extends('sppassport::layouts.frontend')
@section('title', __('sppassport::common.compare_with') . ' ' . $user->name_private)
@section('content')
@if(isset($sppassport_css))
  <link rel="stylesheet" href="{{ $sppassport_css }}">
@endif

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom mb-0"><i class="ph-fill ph-medal-military align-middle fs-20 me-1"></i>@lang('sppassport::common.compare_with') {{ $user->name_private }}<span class="float-end">
                    <a href="{{ route('passport.index') }}" class="btn btn-secondary">@lang('sppassport::common.back')</a>
                   </span></h4>                   
            </div>
        </div>   
    </div>
</div>

@php
    function cmp_icon($diff) {
        if ($diff > 0) {
            return '<i class="ph-fill ph-thumbs-up text-success float-end"></i>';
        } elseif ($diff < 0) {
            return '<i class="ph-fill ph-thumbs-down text-danger float-end"></i>';
        }
        return '<i class="ph-fill ph-circle text-muted float-end"></i>';
    }
    $allContinents = array_unique(array_merge(
        array_keys($continentCoverageMine ?? []),
        array_keys($continentCoverageTheirs ?? [])
    ));
    sort($allContinents);
@endphp

<div class="row">
    <div class="col">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom">
                    <i class="ph-fill ph-user-list align-middle fs-20 me-1"></i> {{ $current->name }}
                </h4>
                                <table class="table table-hover table-striped">
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
                            <img src="{{ asset('sppassport/flags') }}/{{ strtolower($c) }}.svg" alt="{{ $c }}" class="rounded shadow-sm" width="64" height="48">
                            <div class="caption"><span class="caption-text">{{ $c }}</span></div>
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
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom">
                    <i class="ph-fill ph-user-list align-middle fs-20 me-1"></i> {{ $user->name_private }}
                </h4>
                                <table class="table table-hover table-striped">
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
                            <img src="{{ asset('sppassport/flags') }}/{{ strtolower($c) }}.svg" alt="{{ $c }}" class="rounded shadow-sm" width="64" height="48">
                            <div class="caption"><span class="caption-text">{{ $c }}</span></div>
                        </li>
                    @endforeach
                </ul>

            </div>
        </div>
    </div>
</div>

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom mb-0"><i class="ph-fill ph-medal align-middle fs-20 me-1"></i> @lang('sppassport::common.advanced_comparisons')</h4>  
                 <table class="table table-hover table-striped">
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
                                    <i class="bi bi-hand-thumbs-up-fill text-success float-end"></i> @lang('sppassport::common.you')
                                @elseif($firstToVisit === 'Them')
                                    <i class="bi bi-hand-thumbs-down-fill text-danger float-end"></i> @lang('sppassport::common.them')
                                @else
                                    <i class="bi bi-circle-fill text-primary float-end"></i> @lang('sppassport::common.same_time')
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
                                            <span>{{ $mine }}<span class="mx-2">vs.</span>{{ $their }}{!! cmp_icon($diff) !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        <tr class="fw-bold text-center">
                            <td>@lang('sppassport::common.win_counter')</td>
                            <td>
                                @if($winner === 'Tie')
                                    <i class="ph-fill ph-circle text-primary"></i> @lang('sppassport::common.tie')
                                @elseif($winner === 'You')
                                    <i class="ph-fill ph-thumbs-up text-success"></i> @lang('sppassport::common.you_win')
                                @else
                                    <i class="ph-fill ph-thumbs-down text-danger"></i> @lang('sppassport::common.they_win', ['name' => $user->name_private])
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