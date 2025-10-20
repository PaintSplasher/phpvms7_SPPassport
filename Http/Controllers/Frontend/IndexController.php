<?php

namespace Modules\SPPassport\Http\Controllers\Frontend;

use App\Contracts\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\Airport;
use App\Models\User;
use App\Models\Enums\PirepState;
use Carbon\Carbon;
use App\Support\Units\Distance;

class IndexController extends Controller
{
    // Display the main Smart Pilot Passport dashboard.
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cache key for the current user's dashboard data
        $cacheKey = "sppassport_dashboard_{$user->id}";

        // Cache global leaderboard (Top 10 worldwide)
        $leaderboard = Cache::remember('sppassport_global_leaderboard', now()->addMinutes(30), function () {
            return collect($this->getGlobalLeaderboard())
                ->map(fn($item) => (object)$item) // convert everything to objects
                ->values();
        });

        // Cache user-specific stats (dashboard data)
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $this->generatePassportStats($user);
        });

        // Weekly Top Pilots (Top 10 of this week)
        $weeklyTop = Cache::remember('sppassport_weekly_top', now()->addMinutes(30), function () {
            $raw = Pirep::where('state', PirepState::ACCEPTED)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as flights'),
                    DB::raw('SUM(flight_time) as total_flight_time'),
                    DB::raw('SUM(distance) as total_distance')
                )
                ->groupBy('user_id')
                ->orderByDesc('flights')
                ->take(10)
                ->get();

            $userIds = $raw->pluck('user_id')->unique();
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            return $raw->map(function ($pirep) use ($users) {
                $user = $users->get($pirep->user_id);
                if (!$user) return null;

                $distance = new \App\Support\Units\Distance(
                    $pirep->total_distance ?? 0,
                    config('phpvms.internal_units.distance')
                );

                return (object)[
                    'id'          => $user->id,
                    'name'        => $user->name_private,
                    'ident'       => $user->ident,
                    'country'     => $user->country,
                    'flights'     => $pirep->flights,
                    'flight_time' => $pirep->total_flight_time ?? 0,
                    'distance'    => $distance,
                ];
            })->filter()->values();
        });

        // Rival of the Week – Weekly leaderboard first, global leaderboard second
        if ($weeklyTop->contains('id', $user->id)) {
            $currentRank = collect($weeklyTop)->search(fn($p) => $p->id === $user->id);
            $rival = $currentRank > 0 ? $weeklyTop[$currentRank - 1] : null;
        } else {
            $currentRank = collect($leaderboard)->search(fn($p) => $p->user_id === $user->id);
            $rival = $currentRank > 0 ? $leaderboard[$currentRank - 1] : null;
        }

// Rare destinations — least visited countries (only those that have active/visible flights)
$rareCountries = Cache::remember('sppassport_rare_countries', now()->addHours(6), function () {
    $pirepTable   = (new Pirep)->getTable();
    $airportTable = (new Airport)->getTable();
    $flightsTable = (new Flight)->getTable();

    // 1) Finde alle Länder, in denen es mindestens einen aktiven & sichtbaren Flug gibt
    $countriesWithFlights = DB::table($flightsTable)
        ->join($airportTable, function ($join) use ($airportTable, $flightsTable) {
            $join->on("$flightsTable.dpt_airport_id", '=', "$airportTable.id")
                 ->orOn("$flightsTable.arr_airport_id", '=', "$airportTable.id");
        })
        ->where("$flightsTable.active", true)
        ->where("$flightsTable.visible", true)
        ->whereNotNull("$airportTable.country")
        ->select("$airportTable.country")
        ->distinct()
        ->pluck('country')
        ->map(fn($c) => strtoupper(trim($c)))
        ->toArray();

    if (empty($countriesWithFlights)) {
        return collect();
    }

    // 2) Zähle alle akzeptierten PIREPs pro Land, aber nur für Länder mit vorhandenen Flügen
    $countryTotals = DB::table($pirepTable)
        ->join($airportTable, "$airportTable.id", '=', "$pirepTable.arr_airport_id")
        ->where("$pirepTable.state", PirepState::ACCEPTED)
        ->whereIn(DB::raw('UPPER(TRIM(' . $airportTable . '.country))'), $countriesWithFlights)
        ->select(DB::raw('UPPER(TRIM(' . $airportTable . '.country)) as country'))
        ->selectRaw('COUNT(*) as total_flights')
        ->groupBy('country')
        ->orderBy('total_flights', 'asc')
        ->limit(10)
        ->get()
        ->map(fn($row) => (object)[
            'country' => $row->country,
            'flights' => (int) $row->total_flights,
        ]);

    return $countryTotals;
});


        // User list
        $users = User::orderBy('name')->get();

        // Collect data
        $data['leaderboard'] = $leaderboard;
        $data['users'] = $users;
        $data['rival'] = $rival;
        $data['weeklyTop'] = $weeklyTop;
        $data['rareCountries'] = $rareCountries;

        return view('sppassport::index', $data);
    }

    // Generate all passport statistics for a given user.
    protected function generatePassportStats($user): array
    {
        $pireps = Pirep::with('arr_airport')
            ->where('user_id', $user->id)
            ->where('state', PirepState::ACCEPTED)
            ->orderBy('created_at', 'asc')
            ->get();

        // Return empty stats if user has no accepted PIREPs
        if ($pireps->isEmpty()) {
            return $this->emptyData();
        }

        $countryData = [];
        $countryCounts = [];

        // Build statistics per country
        foreach ($pireps as $pirep) {
            $airport = $pirep->arr_airport;
            if (!$airport || empty($airport->country)) {
                continue; // Skip invalid airports
            }

            $country = strtoupper(trim($airport->country));
            $countryCounts[$country] = ($countryCounts[$country] ?? 0) + 1;

            // Record first visit for this country
            if (!isset($countryData[$country])) {
                $countryData[$country] = [
                    'country'       => $country,
                    'first_airport' => $airport->icao ?? '',
                    'first_visit'   => optional($pirep->created_at)->format('d.m.Y'),
                ];
            }
        }

        $countries = array_keys($countryData);
        $visitedCount = count($countries);

        // Get first airport visited per country for map display
        $firstAirportIcaos = array_column($countryData, 'first_airport');
        $airports = $pireps->pluck('arr_airport')
            ->filter()
            ->filter(fn($a) => in_array($a->icao, $firstAirportIcaos))
            ->unique('icao');

        // Count unique airports visited (all, not just first per country)
        $uniqueAirports = $pireps->pluck('arr_airport')->filter()->unique('icao')->count();

        $totalFlights = $pireps->count();

        // Calculate total flight distance safely
        $totalDistanceValue = round(
            $pireps->reduce(function ($carry, $pirep) {
                $dist = $pirep->distance;
                if (is_numeric($dist)) {
                    return $carry + (float) $dist;
                }
                if (is_object($dist) && method_exists($dist, 'internal')) {
                    return $carry + (float) $dist->internal();
                }
                return $carry;
            }, 0),
            1
        );

        // Wrap distance value in Distance object using internal phpVMS unit
        $totalDistance = new Distance($totalDistanceValue, config('phpvms.internal_units.distance'));

        // Failsafe: ensure totalDistance is always a valid Distance object
        if (!$totalDistance instanceof Distance) {
            $totalDistance = new Distance(0, config('phpvms.internal_units.distance'));
        }

        // Calculate total flight time in minutes
        $totalFlightMinutes = $pireps->reduce(function ($carry, $pirep) {
            $time = $pirep->flight_time ?? 0;
            return $carry + (int) $time;
        }, 0);

        $firstFlightDate = optional($pireps->first()->created_at)->format('d.m.Y');
        $lastFlightDate  = optional($pireps->last()->created_at)->format('d.m.Y H:i');

        // Cache list of all available countries
        $allCountries = Cache::remember('sppassport_all_countries', now()->addHours(6), function () {
            return Airport::whereNotNull('country')
                ->selectRaw('DISTINCT UPPER(TRIM(country)) as country')
                ->pluck('country')
                ->toArray();
        });

        $totalCountries = count($allCountries);
        $progress = $totalCountries > 0 ? round(($visitedCount / $totalCountries) * 100, 2) : 0;

        // Determine top 5 most visited countries
        $topCountries = collect($countryCounts)
            ->sortDesc()
            ->take(5)
            ->map(fn($count, $country) => [
                'country' => $country,
                'flights' => $count,
            ])
            ->values()
            ->toArray();

        // Flights per month (for charts)
        $flightsPerMonth = $pireps
            ->groupBy(fn($p) => $p->created_at->format('Y-m'))
            ->map->count()
            ->sortKeys()
            ->toArray();

        // Simple recommendation system — suggest random unvisited countries
        $visitedCountries = $countries;
        $unvisited = array_diff($allCountries, $visitedCountries);
        $recommendations = collect($unvisited)->shuffle()->take(5)->values()->toArray();

        return [
            'provider'            => 'Esri.WorldStreetMap',
            'countries'           => $countries,
            'countryData'         => $countryData,
            'airports'            => $airports->values()->toArray(),
            'allCountries'        => $allCountries,
            'visitedCount'        => $visitedCount,
            'totalCountries'      => $totalCountries,
            'progress'            => round($progress),
            'topCountries'        => $topCountries,
            'totalFlights'        => $totalFlights,
            'totalDistance'       => $totalDistance,
            'totalFlightMinutes'  => $totalFlightMinutes,
            'uniqueAirports'      => $uniqueAirports,
            'firstFlightDate'     => $firstFlightDate,
            'lastFlightDate'      => $lastFlightDate,
            'flightsPerMonth'     => $flightsPerMonth,
            'recommendations'     => $recommendations,
        ];
    }


    // Returns a default empty data structure for new users or no PIREPs.
    protected function emptyData(): array
    {
        $allCountries = Cache::remember('sppassport_all_countries', now()->addHours(6), function () {
            return Airport::whereNotNull('country')
                ->selectRaw('DISTINCT UPPER(TRIM(country)) as country')
                ->pluck('country')
                ->toArray();
        });

        return [
            'provider'           => 'Esri.WorldStreetMap',
            'countries'          => [],
            'countryData'        => [],
            'airports'           => [],
            'allCountries'       => $allCountries,
            'visitedCount'       => 0,
            'totalCountries'     => count($allCountries),
            'progress'           => 0,
            'topCountries'       => [],
            'totalFlights'       => 0,
            'totalDistance'      => new Distance(0, config('phpvms.internal_units.distance')),
            'totalFlightMinutes' => 0,
            'uniqueAirports'     => 0,
            'firstFlightDate'    => null,
            'lastFlightDate'     => null,
            'flightsPerMonth'    => [],
        ];
    }

    // Build a global leaderboard - OPTIMIZED
    protected function getGlobalLeaderboard(): array
    {
        $pirepTable = (new Pirep)->getTable();
        $airportTable = (new Airport)->getTable();

        // Get all user stats (flights, time, distance)
        $userStats = DB::table($pirepTable)
            ->where('state', PirepState::ACCEPTED)
            ->select('user_id')
            ->selectRaw("COUNT(*) as flights, COALESCE(SUM(flight_time), 0) as flight_time, COALESCE(SUM(distance), 0) as distance")
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        if ($userStats->isEmpty()) {
            return [];
        }

        // Get PIREP IDs grouped by user
        $userPirepIds = DB::table($pirepTable)
            ->where('state', PirepState::ACCEPTED)
            ->select('user_id', 'arr_airport_id')
            ->get()
            ->groupBy('user_id');

        // Get all airport IDs we need
        $allAirportIds = $userPirepIds->flatMap(fn($items) => $items->pluck('arr_airport_id'))->unique()->toArray();

        // Load all airports once
        $airports = DB::table($airportTable)
            ->whereIn('id', $allAirportIds)
            ->select('id', 'icao', 'country')
            ->get()
            ->keyBy('id');

        // Count countries and airports per user
        $leaderboard = [];
        foreach ($userPirepIds as $userId => $pireps) {
            $stat = $userStats->get($userId);
            if (!$stat) {
                continue;
            }

            $countries = [];
            $airportSet = [];

            foreach ($pireps as $pirep) {
                $airport = $airports->get($pirep->arr_airport_id);
                if ($airport) {
                    $country = strtoupper(trim($airport->country));
                    $countries[$country] = true;
                    $airportSet[$airport->icao] = true;
                }
            }

            $leaderboard[] = [
                'user_id'     => $userId,
                'countries'   => count($countries),
                'airports'    => count($airportSet),
                'flights'     => (int) $stat->flights,
                'flight_time' => (int) $stat->flight_time,
                'distance'    => round((float) $stat->distance, 1),
            ];
        }

        // Sort by countries, then flights
        usort($leaderboard, fn($a, $b) =>
            $b['countries'] <=> $a['countries'] ?: $b['flights'] <=> $a['flights']
        );

        $top10 = array_slice($leaderboard, 0, 10);

        // Load top 10 users
        $userIds = array_column($top10, 'user_id');
        $users = User::whereIn('id', $userIds)
            ->select('id', 'name', 'country')
            ->get()
            ->keyBy('id');

        $unit = setting('units.distance');

        // Final format with Distance object
        return collect($top10)->map(function ($item) use ($users, $unit) {
            $user = $users->get($item['user_id']);
            if (!$user) {
                return null;
            }

            return [
                'user_id'      => $item['user_id'],
                'user_name'    => $user->name_private,
                'user_country' => $user->country,
                'countries'    => $item['countries'],
                'airports'     => $item['airports'],
                'flights'      => $item['flights'],
                'flight_time'  => $item['flight_time'],
                'distance'     => new Distance($item['distance'], config('phpvms.internal_units.distance')),
            ];
        })->filter()->values()->toArray();
    }

    // Invalidate a specific user's cached dashboard data.
    public static function invalidateUserCache(int $userId): void
    {
        Cache::forget("sppassport_dashboard_{$userId}");
    }
}