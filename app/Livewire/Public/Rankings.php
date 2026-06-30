<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\User;
use App\Models\ScoreTransaction;
use Illuminate\Support\Facades\DB;

class Rankings extends Component
{
    public $type = 'general'; // daily, weekly, monthly, general
    public $district = '';

    protected $queryString = [
        'type' => ['except' => 'general'],
        'district' => ['except' => ''],
    ];

    public function render()
    {
        $queryScore = ScoreTransaction::where('status', 'confirmed');

        // Filter score transactions by time period
        if ($this->type === 'daily') {
            $queryScore->where('created_at', '>=', now()->startOfDay());
        } elseif ($this->type === 'weekly') {
            $queryScore->where('created_at', '>=', now()->startOfWeek());
        } elseif ($this->type === 'monthly') {
            $queryScore->where('created_at', '>=', now()->startOfMonth());
        }

        // Sum points per user
        $scoresMap = $queryScore->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->pluck('total_points', 'user_id');

        // Fetch users that have usernames set
        $users = User::where('is_suspended', false)
            ->whereNotNull('username')
            ->get()
            ->map(function ($user) use ($scoresMap) {
                // If general ranking, use the cached score column on user table, else use period sum
                $user->rank_score = ($this->type === 'general') 
                    ? (int) $user->score 
                    : (int) ($scoresMap[$user->id] ?? 0);
                return $user;
            });

        // Filter users who reported in the specific district (optional)
        if ($this->district && $users->isNotEmpty()) {
            $userIds = $users->pluck('id');
            $districtUserIds = DB::table('flag_reports')
                ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
                ->whereIn('flag_reports.user_id', $userIds)
                ->where(DB::raw('lower(beaches.district)'), strtolower($this->district))
                ->distinct()
                ->pluck('flag_reports.user_id');

            $users = $users->whereIn('id', $districtUserIds)->values();
        }

        // Sort descending and reset indices
        $sortedUsers = $users->sortByDesc('rank_score')->values();

        return view('livewire.public.rankings', [
            'rankingsList' => $sortedUsers,
        ])->layout('components.layouts.app');
    }
}
