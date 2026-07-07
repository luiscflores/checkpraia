<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\User;
use App\Models\ScoreTransaction;
use App\Models\Beach;
use App\Models\FlagReport;
use Illuminate\Support\Facades\DB;

class Rankings extends Component
{
    public $type = 'general';
    public $district = '';
    public $perPage = 50;

    protected $queryString = [
        'type' => ['except' => 'general'],
        'district' => ['except' => ''],
    ];

    public function loadMore(): void
    {
        $this->perPage += 50;
    }

    public function render()
    {
        // 1. Build base period transaction query if needed
        $periodQuery = null;
        if ($this->type !== 'general') {
            $periodQuery = ScoreTransaction::where('status', 'confirmed')
                ->when($this->type === 'daily', fn($q) => $q->where('created_at', '>=', now()->startOfDay()))
                ->when($this->type === 'weekly', fn($q) => $q->where('created_at', '>=', now()->startOfWeek()))
                ->when($this->type === 'monthly', fn($q) => $q->where('created_at', '>=', now()->startOfMonth()));
        }

        // 2. Fetch eligible users sorted by correct score
        if ($this->type === 'general') {
            $usersQuery = User::where('is_suspended', false)
                ->whereNotNull('username')
                ->select('users.*', DB::raw('score as rank_score'))
                ->orderBy('score', 'desc');
        } else {
            // Aggregate score per user in database and left join
            $usersQuery = User::where('users.is_suspended', false)
                ->whereNotNull('users.username')
                ->leftJoinSub(
                    $periodQuery->groupBy('user_id')->select('user_id', DB::raw('SUM(points) as period_score')),
                    'scores',
                    'users.id',
                    '=',
                    'scores.user_id'
                )
                ->select('users.*', DB::raw('COALESCE(scores.period_score, 0) as rank_score'))
                ->orderBy('rank_score', 'desc');
        }

        // 3. Apply district filter using optimized database subquery
        if ($this->district) {
            $usersQuery->whereIn('users.id', function ($q) {
                $q->select('user_id')
                    ->from('flag_reports')
                    ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
                    ->where('beaches.district', $this->district)
                    ->distinct();
            });
        }

        $users = $usersQuery->take($this->perPage + 1)->get();

        $hasMore = $users->count() > $this->perPage;
        $displayedUsers = $users->take($this->perPage);

        // 5. Districts for filter dropdown
        $districts = Beach::whereNotNull('district')
            ->where('district', '!=', '')
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');

        // 6. Current user's position
        $currentUserPosition = null;
        $currentUserScore = null;
        if (auth()->check()) {
            if ($this->type === 'general') {
                $currentUserScore = (int) auth()->user()->score;
                $pos = User::where('is_suspended', false)
                    ->whereNotNull('username')
                    ->where('score', '>', $currentUserScore)
                    ->count();
                $currentUserPosition = $pos + 1;
            } else {
                // Calculate current user's period score
                $currentUserScore = (int) (clone $periodQuery)->where('user_id', auth()->id())->sum('points');

                // Count users with a higher score in the period
                $pos = User::where('is_suspended', false)
                    ->whereNotNull('username')
                    ->joinSub(
                        (clone $periodQuery)->groupBy('user_id')->select('user_id', DB::raw('SUM(points) as total_points')),
                        'scores',
                        'users.id',
                        '=',
                        'scores.user_id'
                    )
                    ->where('scores.total_points', '>', $currentUserScore)
                    ->count();

                $currentUserPosition = $pos + 1;
            }
        }

        return view('livewire.public.rankings', [
            'rankingsList' => $displayedUsers,
            'districts' => $districts,
            'currentUserPosition' => $currentUserPosition,
            'currentUserScore' => $currentUserScore,
            'hasMore' => $hasMore,
        ])->layout('components.layouts.app');
    }
}
