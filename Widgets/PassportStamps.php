<?php

namespace Modules\SPPassport\Widgets;

use App\Contracts\Widget;
use App\Models\PIREP;
use App\Models\User;
use App\Models\Enums\PirepState;
use Carbon\Carbon;

class PassportStamps extends Widget
{
    protected $config = [
        'title'   => 'Passport Stamps',
        'limit'   => 0,     // 0 = show all countries
        'user_id' => null,  // optional override
    ];

    public function run()
    {
        // Determine which user to display data for
        $user = $this->config['user_id']
            ? User::find($this->config['user_id'])
            : auth()->user();

        if (!$user) {
            return 'No user selected or logged in.';
        }

        // Load all accepted PIREPs for this user with destination airport info
        $pireps = PIREP::with('arr_airport')
            ->where('user_id', $user->id)
            ->where('state', PirepState::ACCEPTED)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($pireps->isEmpty()) {
            return 'No accepted PIREPs found for this user.';
        }

        // Extract all unique destination countries
        $countries = $pireps->pluck('arr_airport.country')
            ->filter()
            ->unique()
            ->values();

        // Most recent flight (last passport stamp)
        $last_stamp = $pireps->sortByDesc('created_at')->first();
        $lastStampDate = optional($last_stamp?->created_at)->format('d.m.Y') ?? '-';

        // Build travel history: first visit date per country
        $travel_history = $pireps
            ->filter(fn($p) => optional($p->arr_airport)->country)
            ->groupBy(fn($p) => strtoupper(trim($p->arr_airport->country)))
            ->map(function ($flights, $country) {
                return (object)[
                    'country'     => $country,
                    'first_visit' => $flights->min('created_at'),
                ];
            })
            ->sortBy('first_visit')
            ->values();

        // Apply limit (useful for previews)
        if ($this->config['limit'] > 0) {
            $travel_history = $travel_history->take($this->config['limit']);
        }

        // Group by year of first visit
        $grouped = $travel_history->groupBy(fn($item) =>
            Carbon::parse($item->first_visit)->format('Y')
        );

        // Find best year (most new countries)
        $bestYear = null;
        $mostCountries = 0;
        foreach ($grouped as $year => $entries) {
            if ($entries->count() > $mostCountries) {
                $mostCountries = $entries->count();
                $bestYear = $year;
            }
        }

        // Return data to Blade view
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
