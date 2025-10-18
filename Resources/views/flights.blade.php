@extends('sppassport::layouts.frontend')
@section('title', __('sppassport::common.all_flights_to', ['country' => $country]))
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">{{ trans_choice('sppassport::common.flight', 2) }} - {{ $country }}</h2>
            <span class="float-end">
                <a href="{{ route('passport.index') }}" class="btn btn-secondary">@lang('sppassport::common.back')</a>
            </span>
        </div>
        <div class="card">
            <div class="card-body p-4">
                @if($flights->isNotEmpty())
                <table class="table table-responsive table-striped table-hover">
                    <thead>
                        <tr>
                            <th>@sortablelink('airline_id', __('common.airline'))</th>
                            <th>@sortablelink('flight_number', __('flights.flightnumber'))</th>
                            <th>@sortablelink('dpt_airport_id', __('airports.departure'))</th>
                            <th>@sortablelink('arr_airport_id', __('airports.arrival'))</th>
                            <th class="text-center">@sortablelink('dpt_time', __('sppassport::common.std'))</th>
                            <th class="text-center">@sortablelink('arr_time', __('sppassport::common.sta'))</th>
                            <th class="text-center">@sortablelink('distance', __('sppassport::common.distance'))</th>
                            <th class="text-center">@sortablelink('flight_time', __('sppassport::common.flight_time'))</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flights as $flight)
                        <tr>
                            <td>{{ $flight->airline->icao ?? '—' }}</td>
                            <td>
                                <a href="{{ url('/flights/'.$flight->id) }}" class="text-decoration-none">
                                    {{ $flight->ident }}
                                </a>
                            </td>
                            <td>{{ optional($flight->dpt_airport)->icao ?? '—' }}</td>
                            <td>{{ optional($flight->arr_airport)->icao ?? '—' }}</td>
                            <td class="text-center">{{ $flight->dpt_time ?? '—' }}</td>
                            <td class="text-center">{{ $flight->arr_time ?? '—' }}</td>
                            <td>{{ $flight->distance }} nmi</td>
                            <td class="text-center">@minutestotime($flight->flight_time ?? 0)</td>
                            <td class="text-end">
                                <button
                                    class="btn btn-sm save_flight {{ isset($saved[$flight->id]) ? 'btn-danger' : 'btn-success' }}"
                                    x-id="{{ $flight->id }}"
                                    x-saved-class="btn-danger"
                                    type="button"
                                    title="@lang('flights.addremovebid')">
                                    {{ isset($saved[$flight->id]) ? __('flights.removebid') : __('flights.addbid') }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="text-center text-muted" style="padding: 30px;">
                        @lang('flights.none')
                    </div>                    
                @endif
            </div>
        </div>
        <div class="mt-3">
            {{ $flights->links('pagination::bootstrap-5') }}
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
                <select name="" id="aircraft_select" class="bid_aircraft form-control"></select>
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
