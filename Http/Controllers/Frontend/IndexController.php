<?php

namespace Modules\SPPassport\Http\Controllers\Frontend;

use App\Contracts\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        // Cache global leaderboard separately for 30 minutes
        $leaderboard = Cache::remember('sppassport_global_leaderboard', now()->addMinutes(30), function () {
            return $this->getGlobalLeaderboard();
        });

        // Cache user-specific stats for 10 minutes
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $this->generatePassportStats($user);
        });

        // Load user list (for dropdowns, comparisons, etc.)
        $users = User::orderBy('name')->get();

        // Merge additional data
        $data['leaderboard'] = $leaderboard;
        $data['users'] = $users;

        // Find the "Rival of the Week" (the pilot just above the current user)
        $currentRank = collect($leaderboard)->search(fn($p) => $p['user_id'] === $user->id);
        $rival = $currentRank > 0 ? $leaderboard[$currentRank - 1] : null;

        // Weekly top pilots — count who submitted the most accepted PIREPs this week
        $weeklyTop = Cache::remember('sppassport_weekly_top', now()->addMinutes(30), function () {
            return Pirep::with('user')
                ->where('state', PirepState::ACCEPTED)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as flights'),
                    DB::raw('SUM(flight_time) as total_flight_time'),
                    DB::raw('SUM(distance) as total_distance')
                )
                ->groupBy('user_id')
                ->orderByDesc('flights')
                ->take(5)
                ->get()
                ->map(function ($pirep) {
                    $user = $pirep->user;

                    $unit = setting('units.distance');

                    $distance = new Distance($pirep->total_distance ?? 0, config('phpvms.internal_units.distance'));

                    return (object)[
                        'id'          => $user->id ?? null,
                        'name'        => $user->name_private ?? '-',
                        'country'     => $user->country ?? null,
                        'flights'     => $pirep->flights,
                        'flight_time' => $pirep->total_flight_time ?? 0,
                        'distance'    => $distance,
                    ];
                })
                ->filter(fn($p) => $p->id !== null)
                ->values();
        });

        // Rare destinations — airports that are least frequently visited in all PIREPs
        $rareAirports = Cache::remember('sppassport_rare_airports', now()->addHours(6), function () {
            $pirepTable = (new Pirep)->getTable();
            $airportTable = (new Airport)->getTable();

            // Get airport visit counts
            $airportFlights = DB::table($pirepTable)
                ->where('state', PirepState::ACCEPTED)
                ->select('arr_airport_id')
                ->selectRaw('COUNT(*) as flights')
                ->groupBy('arr_airport_id')
                ->get()
                ->keyBy('arr_airport_id');

            // Get all airports and filter/sort by flight count
            $airports = DB::table($airportTable)
                ->select('id', 'icao', 'country')
                ->get();

            // Map flight counts and sort
            $rareAirports = $airports->map(function ($airport) use ($airportFlights) {
                $flightCount = $airportFlights->get($airport->id)?->flights ?? 0;
                return (object) [
                    'icao'    => $airport->icao,
                    'country' => $airport->country,
                    'flights' => $flightCount,
                ];
            })
            ->sortBy('flights')
            ->sortBy('icao')
            ->take(10)
            ->values();

            return $rareAirports;
        });

        // Merge competitive data into the main dataset for the Blade view
        $data['rival'] = $rival;
        $data['weeklyTop'] = $weeklyTop;
        $data['rareAirports'] = $rareAirports;

        // Share all data globally with Blade partials
        // view()->share($data);

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

    // Build a global leaderboard - OPTIMIERT
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

        // Final format mit Distance-Objekt
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