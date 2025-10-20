<?php

namespace Modules\SPPassport\Widgets;

use App\Contracts\Widget;
use App\Models\PIREP;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PassportStamps extends Widget
{
    protected $config = [
        'title' => 'Passport Stamps',
        'limit' => 0, // 0 = show all countries
        'user_id' => null, // optional override
    ];

    public function run()
    {
        // Determine which user to load
        $user = $this->config['user_id']
            ? User::find($this->config['user_id'])
            : auth()->user();

        if (!$user) {
            return 'No user selected or logged in.';
        }

        // Fetch all unique destination countries from the user's accepted PIREPs
        $countries = PIREP::where('user_id', $user->id)
            ->join('airports as arr', 'pireps.arr_airport_id', '=', 'arr.id')
            ->select('arr.country')
            ->distinct()
            ->pluck('arr.country')
            ->filter()
            ->unique()
            ->values();

        // Fetch the user's most recent flight (last passport stamp)
        $last_stamp = PIREP::where('user_id', $user->id)
            ->join('airports as arr', 'pireps.arr_airport_id', '=', 'arr.id')
            ->select('arr.country', 'pireps.created_at')
            ->orderBy('pireps.created_at', 'desc')
            ->first();

        // Build the user's travel history (first time visiting each country)
        $travel_history = PIREP::where('user_id', $user->id)
            ->join('airports as arr', 'pireps.arr_airport_id', '=', 'arr.id')
            ->select('arr.country', DB::raw('MIN(pireps.created_at) as first_visit'))
            ->groupBy('arr.country')
            ->orderBy('first_visit', 'asc')
            ->get();

        // Apply optional limit (for example, widget preview)
        if ($this->config['limit'] > 0) {
            $travel_history = $travel_history->take($this->config['limit']);
        }

        // Group the travel history by year
        $grouped = $travel_history->groupBy(function ($item) {
            return Carbon::parse($item->first_visit)->format('Y');
        });

        // Calculate the user's best year (the year with the most new countries)
        $bestYear = null;
        $mostCountries = 0;

        foreach ($grouped as $year => $entries) {
            if ($entries->count() > $mostCountries) {
                $mostCountries = $entries->count();
                $bestYear = $year;
            }
        }

        // Format the last passport stamp date
        $lastStampDate = optional(optional($last_stamp)->created_at)->format('d.m.Y') ?? '-';

        //  Return all processed data to the Blade view
        return view('sppassport::widgets.passport_stamps', [
            'title'          => $this->config['title'],
            'countries'      => $countries,
            'last_stamp'     => $last_stamp,
            'lastStampDate'  => $lastStampDate,
            'travel_history' => $travel_history,
            'grouped'        => $grouped,
            'bestYear'       => $bestYear,
            'mostCountries'  => $mostCountries,
            'user'           => $user,
        ]);
    }
}
