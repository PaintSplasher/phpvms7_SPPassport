@extends('sppassport::layouts.frontend')
@section('title', __('sppassport::common.all_flights_to', ['country' => $country]))
@section('content')

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom">
               <img src="https://demo.sass-projects.dev/SPTheme/images/banner/10.jpg" class="img-fluid card-img rounded mb-3" width="1920" height="200" alt="Banner / Image">
                    <i class="ph-fill ph-medal-military align-middle fs-20 me-1"></i>{{ trans_choice('sppassport::common.flight', 2) }} - {{ $country }}<span class="float-end">
                    <a href="{{ route('passport.index') }}" class="btn btn-secondary">@lang('sppassport::common.back')</a>
                </span>
                </h4>
                @if($flights->isNotEmpty())
                <table class="table table-responsive table-striped table-hover">
                    <thead>
                        <tr>
                            <th>@sortablelink('airline_id', __('common.airline'))</th>
                            <th>@sortablelink('flight_number', __('flights.flightnumber'))</th>
                            <th class="text-center">@sortablelink('dpt_airport_id', __('airports.departure'))</th>
                            <th class="text-center">@sortablelink('arr_airport_id', __('airports.arrival'))</th>
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
                            <td>
                                @if (optional($flight->airline)->logo)
                                <img src="{{ $flight->airline->logo }}" alt="{{ $flight->airline->name }}" width="90">
                                @else
                                {{ $flight->airline->name }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ url('/flights/'.$flight->id) }}" class="text-decoration-none">
                                    {{ $flight->ident }}
                                </a>
                            </td>                         
                            <td class="text-center"><a href="{{ route('frontend.airports.show', [optional($flight->dpt_airport)->icao]) }}" title="{{ optional($flight->dpt_airport)->icao }}" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> {{ optional($flight->dpt_airport)->icao }}</a></td>
                            <td class="text-center"><a href="{{ route('frontend.airports.show', [optional($flight->arr_airport)->icao]) }}" title="{{ optional($flight->arr_airport)->icao }}" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> {{ optional($flight->arr_airport)->icao }}</a></td>
                            <td class="text-center">{{ $flight->dpt_time ?? '—' }}</td>
                            <td class="text-center">{{ $flight->arr_time ?? '—' }}</td>
                            <td class="text-center"><i class="ph-fill ph-arrows-horizontal align-middle fs-20 me-1"></i>{{ $flight->distance->local(0).' '.setting('units.distance') }}</td>
                            <td class="text-center"><i class="ph-fill ph-clock-clockwise align-middle fs-20 me-1"></i>@minutestotime($flight->flight_time ?? 0)</td>
                            <td class="text-end">
                            @if(!setting('pilots.only_flights_from_current') || $flight->dpt_airport_id === Auth::user()->curr_airport_id)
                            <a class="btn save_flight {{ $flight->has_bid ? 'btn-danger' : 'btn-success' }}"
                            onclick="AddRemoveBid('{{ $flight->has_bid ? 'remove' : 'add' }}', '{{ $flight->id }}')">
                            {{ $flight->has_bid ? __('flights.removebid') : __('flights.addbid') }}
                            </a>
                            @endif
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
         <div class="card-footer">
            {{ $flights->links('pagination::bootstrap-5') }}
        </div>
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
<script>
async function AddRemoveBid(action, flight_id) {
   if (action === "add") {
      await phpvms.bids.addBid(flight_id);
      
      const Toast = Swal.mixin({
         toast: true,
         position: "top-end",
         showConfirmButton: false,
         timer: 3000,
         animation: true,
         iconColor: 'white',
         customClass: {
            popup: 'colored-toast',
         },
         timerProgressBar: true,
         didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
         }
      });
      
      Toast.fire({
         icon: "success",
         title: "@lang('flights.bidadded')"
      });

      setTimeout(() => {
         location.reload();
      }, 3000);

   } else {
      await phpvms.bids.removeBid(flight_id);
      
      const Toast = Swal.mixin({
         toast: true,
         position: "top-end",
         showConfirmButton: false,
         timer: 3000,
         animation: true,
         iconColor: 'white',
         customClass: {
            popup: 'colored-toast',
         },
         timerProgressBar: true,
         didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
         }
      });
      
      Toast.fire({
         icon: "info",
         title: "@lang('flights.bidremoved')"
      });

      setTimeout(() => {
         location.reload();
      }, 3000);
   }
}
</script>
@endsection

@include('flights.scripts')
