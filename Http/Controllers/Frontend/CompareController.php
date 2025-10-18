<?php

namespace Modules\SPPassport\Http\Controllers\Frontend;

use App\Contracts\Controller;
use App\Models\Pirep;
use App\Models\User;
use App\Models\Airport;
use App\Models\Enums\PirepState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CompareController extends Controller
{
    public function show(User $user)
    {
        $current = auth()->user();

        // Prevent comparing the same user
        if ($current->id === $user->id) {
            return redirect()->route('frontend.dashboard')
                ->with('error', 'You cannot compare yourself with your own profile.');
        }

        // Retrieve chronological sequences of visited countries (for streaks)
        $currentCountrySeq = Pirep::where('user_id', $current->id)
            ->where('state', PirepState::ACCEPTED)
            ->join('airports as arr', 'pireps.arr_airport_id', '=', 'arr.id')
            ->orderBy('pireps.created_at', 'asc')
            ->pluck('arr.country');

        $otherCountrySeq = Pirep::where('user_id', $user->id)
            ->where('state', PirepState::ACCEPTED)
            ->join('airports as arr', 'pireps.arr_airport_id', '=', 'arr.id')
            ->orderBy('pireps.created_at', 'asc')
            ->pluck('arr.country');

        // Unique country sets for each user
        $currentCountries = $currentCountrySeq->filter()->unique()->values();
        $otherCountries   = $otherCountrySeq->filter()->unique()->values();

        $myCount    = $currentCountries->count();
        $theirCount = $otherCountries->count();

        // Compare country lists
        $common     = $currentCountries->intersect($otherCountries)->values();
        $onlyMine   = $currentCountries->diff($otherCountries)->values();
        $onlyTheirs = $otherCountries->diff($currentCountries)->values();

        // Similarity score
        $totalUnique = $currentCountries->merge($otherCountries)->unique()->count();
        $similarity  = $totalUnique > 0 ? round(($common->count() / $totalUnique) * 100, 1) : 0;

        // Continent mapping (simplified)
        $continentMap = [
            'Europe'         => ['DE','FR','UK','ES','IT','CH','NL','BE','AT','NO','SE','DK','PL','CZ','PT','IE','FI','GR','HU'],
            'Asia'           => ['CN','JP','KR','IN','TH','VN','SG','MY','PH','ID','AE','SA'],
            'Africa'         => ['EG','ZA','MA','NG','KE','TZ'],
            'North America'  => ['US','CA','MX'],
            'South America'  => ['BR','AR','CL','CO','PE'],
            'Oceania'        => ['AU','NZ','FJ'],
        ];

        $getContinentsVisited = function (Collection $countries) use ($continentMap) {
            $result = [];
            foreach ($continentMap as $continent => $codes) {
                $count = $countries->intersect($codes)->count();
                if ($count > 0) {
                    $result[$continent] = $count;
                }
            }
            return $result;
        };

        $myContCoverageArr     = $getContinentsVisited($currentCountries);
        $theirContCoverageArr  = $getContinentsVisited($otherCountries);
        $myContinentsCount     = count($myContCoverageArr);
        $theirContinentsCount  = count($theirContCoverageArr);
        $continentOverlap      = count(array_intersect(array_keys($myContCoverageArr), array_keys($theirContCoverageArr)));

        // Additional metrics
        $sharedCount       = $common->count();
        $overlapPercentage = $totalUnique > 0 ? round(($sharedCount / $totalUnique) * 100, 1) : 0;
        $rankDifference    = $current->rank_id !== $user->rank_id ? 'Different' : 'Same';
        $joinDifference    = $current->created_at->diffInDays($user->created_at);

        // Dynamic world coverage based on all countries in the airports table
        $allCountries = Cache::remember('sppassport_all_countries', now()->addHours(6), function () {
            return Airport::whereNotNull('country')
                ->selectRaw('DISTINCT UPPER(TRIM(country)) as country')
                ->pluck('country')
                ->toArray();
        });

        $totalWorldCountries = count($allCountries) > 0 ? count($allCountries) : 195;

        $worldCoverageMine   = $totalWorldCountries > 0 ? round(($myCount / $totalWorldCountries) * 100, 1) : 0.0;
        $worldCoverageTheirs = $totalWorldCountries > 0 ? round(($theirCount / $totalWorldCountries) * 100, 1) : 0.0;

        // Longest unique country streak (based on chronological sequence)
        $calculateStreak = function (Collection $seq) {
            $last = null;
            $streak = 0;
            $max = 0;
            foreach ($seq as $c) {
                if ($c !== $last) {
                    $streak++;
                    $max = max($max, $streak);
                } else {
                    $streak = 1;
                }
                $last = $c;
            }
            return $max;
        };

        $longestStreakMine   = $calculateStreak($currentCountrySeq);
        $longestStreakTheirs = $calculateStreak($otherCountrySeq);

        // First to visit (earliest recorded PIREP)
        $firstMyDate     = Pirep::where('user_id', $current->id)->min('created_at');
        $firstTheirDate  = Pirep::where('user_id', $user->id)->min('created_at');
        $firstToVisit    = 'Tie';
        if ($firstMyDate && $firstTheirDate) {
            $firstToVisit = $firstMyDate < $firstTheirDate ? 'You' : ($firstMyDate > $firstTheirDate ? 'Them' : 'Tie');
        }

        // Most recent new country for each user
        $mostRecentMine   = $this->getMostRecentNewCountry($current->id);
        $mostRecentTheirs = $this->getMostRecentNewCountry($user->id);

        // Unique advantage (difference in exclusive countries)
        $uniqueAdvantage = $onlyMine->count() - $onlyTheirs->count();

        // Simple points-based comparison to determine winner
        $scoreMine = 0;
        $scoreTheirs = 0;

        if ($myCount !== $theirCount) {
            $myCount > $theirCount ? $scoreMine++ : $scoreTheirs++;
        }

        if ($myContinentsCount !== $theirContinentsCount) {
            $myContinentsCount > $theirContinentsCount ? $scoreMine++ : $scoreTheirs++;
        }

        if ($worldCoverageMine !== $worldCoverageTheirs) {
            $worldCoverageMine > $worldCoverageTheirs ? $scoreMine++ : $scoreTheirs++;
        }

        if ($longestStreakMine !== $longestStreakTheirs) {
            $longestStreakMine > $longestStreakTheirs ? $scoreMine++ : $scoreTheirs++;
        }

        if ($uniqueAdvantage !== 0) {
            $uniqueAdvantage > 0 ? $scoreMine++ : $scoreTheirs++;
        }

        if ($firstToVisit !== 'Tie') {
            $firstToVisit === 'You' ? $scoreMine++ : $scoreTheirs++;
        }

        $winner = 'Tie';
        if ($scoreMine > $scoreTheirs) {
            $winner = 'You';
        } elseif ($scoreTheirs > $scoreMine) {
            $winner = 'Them';
        }

        $totalComparisons = 7;
        $winCount = $scoreMine;

        // Render comparison view
        return view('sppassport::compare', [
            'current'                 => $current,
            'user'                    => $user,
            'common'                  => $common,
            'onlyMine'                => $onlyMine,
            'onlyTheirs'              => $onlyTheirs,
            'similarity'              => $similarity,
            'myCount'                 => $myCount,
            'theirCount'              => $theirCount,
            'myContinents'            => $myContinentsCount,
            'theirContinents'         => $theirContinentsCount,
            'continentCoverageMine'   => $myContCoverageArr,
            'continentCoverageTheirs' => $theirContCoverageArr,
            'continentOverlap'        => $continentOverlap,
            'sharedCount'             => $sharedCount,
            'overlapPercentage'       => $overlapPercentage,
            'rankDifference'          => $rankDifference,
            'joinDifference'          => $joinDifference,
            'worldCoverageMine'       => round($worldCoverageMine),
            'worldCoverageTheirs'     => round($worldCoverageTheirs),
            'longestStreakMine'       => $longestStreakMine,
            'longestStreakTheirs'     => $longestStreakTheirs,
            'firstToVisit'            => $firstToVisit,
            'mostRecentMine'          => $mostRecentMine,
            'mostRecentTheirs'        => $mostRecentTheirs,
            'uniqueAdvantage'         => $uniqueAdvantage,
            'winCount'                => $winCount,
            'totalComparisons'        => $totalComparisons,
            'winner'                  => $winner,
        ]);
    }

    // Get the most recently added new country for a given user.
    private function getMostRecentNewCountry(int $userId): string
    {
        $pireps = Pirep::where('user_id', $userId)
            ->where('state', PirepState::ACCEPTED)
            ->join('airports as arr', 'pireps.arr_airport_id', '=', 'arr.id')
            ->orderBy('pireps.created_at', 'desc')
            ->get(['arr.country', 'pireps.created_at']);

        $seen = [];
        foreach ($pireps as $p) {
            $code = $p->country;
            if (!in_array($code, $seen, true)) {
                return $code . ' (' . $p->created_at->diffForHumans() . ')';
            }
            $seen[] = $code;
        }
        return '—';
    }
}
