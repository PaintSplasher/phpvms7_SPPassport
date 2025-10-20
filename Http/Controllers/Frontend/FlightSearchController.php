<?php

namespace Modules\SPPassport\Http\Controllers\Frontend;

use App\Contracts\Controller;
use App\Models\Flight;
use App\Models\Airport;
use App\Models\Bid;
use Illuminate\Support\Facades\Auth;

class FlightSearchController extends Controller
{
    // Search for flights related to a given country.
    public function searchByCountry(string $country)
    {
        // Normalize the input to uppercase and trim extra spaces
        $country = strtoupper(trim($country));

        // Get the current user's saved flight bids
        $saved = Bid::where('user_id', Auth::id())
            ->pluck('flight_id')
            ->flip()
            ->toArray();

        // Find all airport IDs within the given country
        $airportIds = Airport::where('country', $country)->pluck('id');

        // Fetch flights that either depart from OR arrive in those airports,
        // and ensure they are active and visible
        $flights = Flight::with(['dpt_airport', 'arr_airport', 'airline'])
            ->where('active', true)
            ->where('visible', true)
            ->where(function ($query) use ($airportIds) {
                $query->whereIn('dpt_airport_id', $airportIds)
                      ->orWhereIn('arr_airport_id', $airportIds);
            })
            ->sortable()
            ->paginate(20);

        // Render the flight list view
        return view('sppassport::flights', [
            'country' => $country,
            'flights' => $flights,
            'saved'   => $saved,
            'error'   => null,
        ]);
    }
}
