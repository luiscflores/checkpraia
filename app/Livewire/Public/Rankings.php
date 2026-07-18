<?php

namespace App\Livewire\Public;

use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\ScoreTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Rankings extends Component
{
    public $type = 'general';

    public $district = '';

    public $search = '';

    public $perPage = 50;

    public $showUserModal = false;

    public $selectedUser = null;

    public $userBeaches = [];

    public $userPosition = null;

    public $userRankScore = null;

    protected $queryString = [
        'type' => ['except' => 'general'],
        'district' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function loadMore(): void
    {
        $this->perPage += 50;
    }

    public function openUser(int $userId, int $position, int $rankScore): void
    {
        $user = User::where('is_suspended', false)
            ->whereNotNull('username')
            ->find($userId);

        if (! $user) {
            return;
        }

        $this->selectedUser = $user;
        $this->userPosition = $position;
        $this->userRankScore = $rankScore;

        // Cache user beach activity per user — 10 min
        $this->userBeaches = Cache::remember("user_beaches:{$userId}", 600, function () use ($userId) {
            return FlagReport::where('user_id', $userId)
                ->where('status', 'confirmed')
                ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
                ->select(
                    'beaches.id',
                    'beaches.name',
                    'beaches.district',
                    'beaches.municipality',
                    DB::raw('COUNT(*) as confirmations'),
                    DB::raw('MAX(flag_reports.reported_at) as last_report')
                )
                ->groupBy('beaches.id', 'beaches.name', 'beaches.district', 'beaches.municipality')
                ->orderBy('confirmations', 'desc')
                ->get()
                ->toArray();
        });

        $this->showUserModal = true;
    }

    public function closeUserModal(): void
    {
        $this->showUserModal = false;
        $this->selectedUser = null;
        $this->userBeaches = [];
    }

    public function render()
    {
        // 1. Build base period transaction query if needed
        $periodQuery = null;
        if ($this->type !== 'general') {
            $periodQuery = ScoreTransaction::where('status', 'confirmed')
                ->when($this->type === 'daily', fn ($q) => $q->where('created_at', '>=', now()->startOfDay()))
                ->when($this->type === 'weekly', fn ($q) => $q->where('created_at', '>=', now()->startOfWeek()))
                ->when($this->type === 'monthly', fn ($q) => $q->where('created_at', '>=', now()->startOfMonth()));
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

        // 4. Apply search filter
        if ($this->search !== '') {
            $usersQuery->where('username', 'like', '%'.$this->search.'%');
        }

        $cacheKey = 'rankings_users:v2:' . $this->type . ':' . ($this->district ?: '') . ':' . md5($this->search) . ':' . $this->perPage;
        $users = Cache::remember($cacheKey, 300, function () use ($usersQuery) {
            return $usersQuery->take($this->perPage + 1)->get();
        });

        $hasMore = $users->count() > $this->perPage;
        $displayedUsers = $users->take($this->perPage);

        // 5. Districts for filter dropdown — cache 1 hour (nearly immutable data)
        $districts = Cache::remember('rankings_districts', 3600, function () {
            return Beach::whereNotNull('district')
                ->where('district', '!=', '')
                ->select('district')
                ->distinct()
                ->orderBy('district')
                ->pluck('district');
        });

        // 6. Current user's position + confirmed beaches
        $currentUserPosition = null;
        $currentUserScore = null;
        $currentUserBeaches = [];

        if (auth()->check()) {
            $userId = auth()->id();
            if ($this->type === 'general') {
                $currentUserScore = (int) auth()->user()->score;
                $pos = User::where('is_suspended', false)
                    ->whereNotNull('username')
                    ->where('score', '>', $currentUserScore)
                    ->count();
                $currentUserPosition = $pos + 1;
            } else {
                // Calculate current user's period score
                $currentUserScore = (int) (clone $periodQuery)->where('user_id', $userId)->sum('points');

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

            // Fetch confirmed beaches for the share card — cache 10 min per user
            $currentUserBeaches = Cache::remember("user_beaches_share:{$userId}", 600, function () use ($userId) {
                return FlagReport::where('user_id', $userId)
                    ->where('status', 'confirmed')
                    ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
                    ->select('beaches.name', DB::raw('COUNT(*) as count'))
                    ->groupBy('beaches.name')
                    ->orderBy('count', 'desc')
                    ->limit(8)
                    ->pluck('count', 'name')
                    ->toArray();
            });
        }

        return view('livewire.public.rankings', [
            'rankingsList' => $displayedUsers,
            'districts' => $districts,
            'currentUserPosition' => $currentUserPosition,
            'currentUserScore' => $currentUserScore,
            'currentUserBeaches' => $currentUserBeaches,
            'hasMore' => $hasMore,
        ])->layout('components.layouts.app');
    }
}
