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
        // 1. Build period-filtered score map (only for non-general types)
        $scoresMap = [];
        if ($this->type !== 'general') {
            $queryScore = ScoreTransaction::where('status', 'confirmed');

            if ($this->type === 'daily') {
                $queryScore->where('created_at', '>=', now()->startOfDay());
            } elseif ($this->type === 'weekly') {
                $queryScore->where('created_at', '>=', now()->startOfWeek());
            } elseif ($this->type === 'monthly') {
                $queryScore->where('created_at', '>=', now()->startOfMonth());
            }

            $scoresMap = $queryScore->groupBy('user_id')
                ->select('user_id', DB::raw('SUM(points) as total_points'))
                ->pluck('total_points', 'user_id');
        }

        // 2. Fetch eligible users, sorted by score
        $query = User::where('is_suspended', false)->whereNotNull('username');

        if ($this->type === 'general') {
            $query->orderBy('score', 'desc');
        }

        // 3. Apply district filter by joining through flag_reports + beaches
        if ($this->district) {
            $districtUserIds = FlagReport::whereHas('beach', function ($q) {
                $q->where(DB::raw('lower(district)'), strtolower($this->district));
            })->select('user_id')->distinct()->pluck('user_id');

            $query->whereIn('id', $districtUserIds);
        }

        $users = $query->take($this->perPage + 50)->get();

        // 4. Assign rank scores
        $allUsers = $users->map(function ($user) use ($scoresMap) {
            $user->rank_score = ($this->type === 'general')
                ? (int) $user->score
                : (int) ($scoresMap[$user->id] ?? 0);
            return $user;
        });

        if ($this->type !== 'general') {
            $allUsers = $allUsers->sortByDesc('rank_score')->values();
        }

        $hasMore = $allUsers->count() > $this->perPage;
        $displayedUsers = $allUsers->take($this->perPage);

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
                $pos = User::where('is_suspended', false)
                    ->whereNotNull('username')
                    ->where('score', '>', auth()->user()->score)
                    ->count();
                $currentUserPosition = $pos + 1;
                $currentUserScore = (int) auth()->user()->score;
            } else {
                foreach ($allUsers as $i => $u) {
                    if ($u->id === auth()->id()) {
                        $currentUserPosition = $i + 1;
                        $currentUserScore = $u->rank_score;
                        break;
                    }
                }
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
