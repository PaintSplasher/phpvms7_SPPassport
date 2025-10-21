@extends('sppassport::layouts.frontend')

@section('title', __('sppassport::common.all_flights_to', ['country' => $country]))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-2">
                <div class="card-header p-1">
                    <h5 class="m-1">{{ trans_choice('sppassport::common.flight', 2) }} - {{ $country }}</h5>
                </div>

                <div class="card-body p-0 table-responsive">
                    @if($flights->isNotEmpty())
                        <table class="table table-sm table-borderless table-striped align-middle text-start text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>@sortablelink('flight_number', __('flights.flightnumber'))</th>
                                    <th>@sortablelink('dpt_airport_id', __('airports.departure'))</th>
                                    <th class="text-center">@sortablelink('dpt_time', 'STD')</th>
                                    <th class="text-center">@sortablelink('arr_time', 'STA')</th>
                                    <th>@sortablelink('arr_airport_id', __('airports.arrival'))</th>
                                    <th class="text-end pe-2">@lang('disposable.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($flights as $flight)
                                    <tr>
                                        <td>                                            
                                            <a href="{{ url('/flights/'.$flight->id) }}" class="text-decoration-none">
                                                {{ $flight->ident ?? '-'}}
                                            </a>
                                        </td>
                                        <td>
                                            @if(Theme::getSetting('flights_flags'))
                                            <img class="img-mh25 mx-1" title="{{ strtoupper(optional($flight->dpt_airport)->country) }}" src="{{ public_asset('/image/flags_new/'.strtolower(optional($flight->dpt_airport)->country).'.png') }}" alt=""/>
                                            @endif
                                            <a href="{{ route('frontend.airports.show', [optional($flight->dpt_airport)->icao]) }}">{{ $flight->dpt_airport->full_name ?? $flight->dpt_airport_id }}</a>
                                        </td>
                                        <td class="text-center">{{ $flight->dpt_time ?? '—' }}</td>
                                        <td class="text-center">{{ $flight->arr_time ?? '—' }}</td>
                                        <td>
                                            @if(Theme::getSetting('flights_flags'))
                                            <img class="img-mh25 mx-1" title="{{ strtoupper(optional($flight->arr_airport)->country) }}" src="{{ public_asset('/image/flags_new/'.strtolower(optional($flight->arr_airport)->country).'.png') }}" alt=""/>
                                            @endif
                                            <a href="{{ route('frontend.airports.show', [optional($flight->arr_airport)->icao]) }}">{{ $flight->arr_airport->full_name ?? $flight->arr_airport_id }}</a>
                                        </td>
                                        <td class="text-end">
                                            <button
                                                class="btn btn-sm m-0 mx-1 p-0 px-1 save_flight {{ isset($saved[$flight->id]) ? 'btn-danger' : 'btn-success' }}"
                                                x-id="{{ $flight->id }}"
                                                x-saved-class="btn-danger"
                                                type="button"
                                                title="@lang('flights.addremovebid')">
                                                <i class="fas fa-map-marker"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center" style="padding: 30px;">
                            @lang('flights.none')
                        </div>
                    @endif
                </div>
            </div>          
            <div class="text-center">
                {{ $flights->withQueryString()->links('pagination.auto') }}
            </div>
        </div>
    </div>

    @if (setting('bids.block_aircraft', false))
        <div class="modal fade" id="bidModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="addBidLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bidModalLabel">@lang('flights.aircraftbooking')</h5>
                        <button type="button" class="btn-close" id="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <select id="aircraft_select" class="bid_aircraft form-control"></select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="without_aircraft" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('flights.dontbookaircraft')
                        </button>
                        <button type="button" id="with_aircraft" class="btn btn-primary" data-bs-dismiss="modal">
                            @lang('flights.bookaircraft')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@include('flights.scripts')
