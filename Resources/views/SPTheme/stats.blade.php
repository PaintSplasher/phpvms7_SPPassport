<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold">{{ $progress }}%</h4>
                <p class="mb-0">@lang('sppassport::common.world_coverage')</p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>
<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold">@minutestotime($totalFlightMinutes)</h4>
                <p class="mb-0">@lang('sppassport::common.flight_time')</p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>
<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold">{{ (int) $totalDistance }} {{ setting('units.distance') }}</h4>
                <p class="mb-0">@lang('sppassport::common.total_distance')</p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>
<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold">{{ $uniqueAirports }}</h4>
                <p class="mb-0">@lang('sppassport::common.airports_visited')</p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>