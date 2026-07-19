<?php

namespace App\Livewire\Admin;

use App\Domain\Community\ConsensusResolver;
use App\Jobs\FetchInfoAguaData;
use App\Jobs\FetchIpmaForecasts;
use App\Models\AdminScoreAdjustment;
use App\Models\Beach;
use App\Models\BeachCurrentStatus;
use App\Models\FlagPrediction;
use App\Models\FlagReport;
use App\Models\OfficialAlert;
use App\Models\ScoreTransaction;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    // ─── Tab Navigation ───
    public $activeTab = 'visao-geral';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── User Management ───
    public $searchUser = '';

    public $selectedUserId;

    public $selectedUser;

    public $adjustmentPoints;

    public $justification;

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::find($userId);
        if ($this->selectedUser) {
            $this->adjustmentPoints = $this->selectedUser->score;
        }
    }

    public function adjustScore()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'adjustmentPoints' => 'required|integer|min:0',
            'justification' => 'required|string|min:4',
        ]);

        $admin = Auth::user();
        $targetUser = $this->selectedUser;
        $prevPoints = $targetUser->score;
        $newPoints = (int) $this->adjustmentPoints;
        $difference = $newPoints - $prevPoints;

        DB::transaction(function () use ($admin, $targetUser, $prevPoints, $newPoints, $difference) {
            ScoreTransaction::create([
                'user_id' => $targetUser->id,
                'type' => 'admin_adjustment',
                'points' => $difference,
                'status' => 'confirmed',
                'description' => __('beach.admin_score_adjustment_description', ['justification' => $this->justification]),
            ]);

            AdminScoreAdjustment::create([
                'admin_user_id' => $admin->id,
                'target_user_id' => $targetUser->id,
                'previous_points' => $prevPoints,
                'new_points' => $newPoints,
                'difference' => $difference,
                'justification' => $this->justification,
            ]);

            $targetUser->score = $newPoints;
            $targetUser->save();
        });

        session()->flash('adjust_success', __('beach.admin_score_adjusted', ['username' => $targetUser->username, 'points' => $newPoints]));
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->justification = '';
    }

    public function toggleSuspension($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->is_suspended = ! $user->is_suspended;
            $user->save();
            session()->flash('user_action', __('beach.admin_suspension_changed', ['username' => $user->username]));
        }
    }

    public function makeAdmin($userId)
    {
        $user = User::find($userId);
        if ($user && ! $user->is_admin) {
            $user->is_admin = true;
            $user->save();
            session()->flash('user_action', __('beach.admin_promoted', ['username' => $user->username]));
        }
    }

    public function removeAdmin($userId)
    {
        if ($userId === Auth::id()) {
            session()->flash('sync_error', __('beach.admin_cannot_remove_own'));

            return;
        }
        $user = User::find($userId);
        if ($user && $user->is_admin) {
            $user->is_admin = false;
            $user->save();
            session()->flash('user_action', __('beach.admin_privileges_removed', ['username' => $user->username]));
        }
    }

    public function resetUserSearch()
    {
        $this->searchUser = '';
        $this->resetPage();
    }

    // ─── Beach Management ───
    public $searchBeach = '';

    public $showInactiveOnly = false;

    public $confirmResetBeachId = null;

    public $overrideBeachId = null;

    public $overrideFlag = 'green';

    public function toggleBeachActive($beachId)
    {
        $beach = Beach::with('translations')->find($beachId);
        if ($beach) {
            $beach->is_active = ! $beach->is_active;
            $beach->save();
            session()->flash('beach_action', $beach->is_active ? __('beach.admin_beach_activated', ['name' => $beach->name]) : __('beach.admin_beach_deactivated', ['name' => $beach->name]));
        }
    }

    public function resetBeachSearch()
    {
        $this->searchBeach = '';
        $this->showInactiveOnly = false;
        $this->confirmResetBeachId = null;
        $this->overrideBeachId = null;
        $this->resetPage();
    }

    public function confirmResetVotes($beachId)
    {
        $this->confirmResetBeachId = $beachId;
    }

    public function cancelResetVotes()
    {
        $this->confirmResetBeachId = null;
    }

    public function resetTodayVotes($beachId)
    {
        $beach = Beach::with('translations')->findOrFail($beachId);

        FlagReport::where('beach_id', $beachId)
            ->where('reported_at', '>=', now()->startOfDay())
            ->where('status', '!=', 'cancelled')
            ->update(['status' => 'cancelled', 'resolved_at' => now()]);

        $resolver = new ConsensusResolver;
        $resolver->resolveCurrentStatus($beach);

        $this->confirmResetBeachId = null;
        session()->flash('beach_action', __('beach.admin_votes_cancelled', ['name' => $beach->name]));
    }

    public function showOverride($beachId)
    {
        $this->overrideBeachId = $beachId;
        $beach = Beach::with('currentStatus')->find($beachId);
        $this->overrideFlag = $beach?->currentStatus?->flag ?? 'green';
    }

    public function cancelOverride()
    {
        $this->overrideBeachId = null;
    }

    public function applyOverride()
    {
        $this->validate(['overrideFlag' => 'required|in:green,yellow,red,blue_or_neutral']);

        $user = Auth::user();
        $beach = Beach::with('translations')->findOrFail($this->overrideBeachId);

        FlagReport::create([
            'user_id' => $user->id,
            'beach_id' => $beach->id,
            'flag' => $this->overrideFlag,
            'vote_weight' => 10,
            'status' => 'confirmed',
            'latitude' => (float) $beach->latitude,
            'longitude' => (float) $beach->longitude,
            'distance_to_beach' => 0,
            'reported_at' => now(),
            'resolved_at' => now(),
        ]);

        $status = BeachCurrentStatus::firstOrNew(['beach_id' => $beach->id]);
        $status->fill([
            'source' => 'admin',
            'flag' => $this->overrideFlag,
            'confidence' => 100,
            'consensus_reports_count' => 0,
            'reason' => __('beach.admin_manual_override'),
        ]);
        $status->save();

        $this->overrideBeachId = null;
        session()->flash('beach_action', __('beach.admin_flag_overridden', ['name' => $beach->name, 'flag' => $this->overrideFlag]));
    }

    // ─── Settings Management ───
    public $newSettingKey = '';

    public $newSettingValue = '';

    public $editingSetting = null;

    public $editSettingValue = '';

    public function addSetting()
    {
        $this->validate([
            'newSettingKey' => 'required|string|max:255|unique:settings,key',
            'newSettingValue' => 'required|string',
        ]);

        Setting::create([
            'key' => $this->newSettingKey,
            'value' => $this->newSettingValue,
        ]);

        session()->flash('settings_success', __('beach.admin_setting_created', ['key' => $this->newSettingKey]));
        $this->newSettingKey = '';
        $this->newSettingValue = '';
    }

    public function editSetting($settingId)
    {
        $this->editingSetting = Setting::find($settingId);
        if ($this->editingSetting) {
            $this->editSettingValue = $this->editingSetting->value;
        }
    }

    public function saveSetting()
    {
        $this->validate([
            'editSettingValue' => 'required|string',
        ]);

        if ($this->editingSetting) {
            $this->editingSetting->value = $this->editSettingValue;
            $this->editingSetting->save();
            session()->flash('settings_success', __('beach.admin_setting_updated', ['key' => $this->editingSetting->key]));
            $this->editingSetting = null;
            $this->editSettingValue = '';
        }
    }

    public function cancelEditSetting()
    {
        $this->editingSetting = null;
        $this->editSettingValue = '';
    }

    public function deleteSetting($settingId)
    {
        $setting = Setting::find($settingId);
        if ($setting) {
            $setting->delete();
            session()->flash('settings_success', __('beach.admin_setting_deleted', ['key' => $setting->key]));
        }
    }

    // ─── Cache Management ───
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            session()->flash('cache_success', __('beach.admin_cache_cleared'));
        } catch (\Exception $e) {
            session()->flash('sync_error', __('beach.admin_cache_clear_error', ['error' => $e->getMessage()]));
        }
    }

    public function clearAllCache()
    {
        try {
            Artisan::call('optimize:clear');
            $output = Artisan::output();
            session()->flash('cache_success', __('beach.admin_cache_total_cleared', ['output' => nl2br(e($output))]));
        } catch (\Exception $e) {
            session()->flash('sync_error', __('beach.admin_cache_total_clear_error', ['error' => $e->getMessage()]));
        }
    }

    public function clearViewCache()
    {
        try {
            Artisan::call('view:clear');
            session()->flash('cache_success', __('beach.admin_view_cache_cleared'));
        } catch (\Exception $e) {
            session()->flash('sync_error', __('beach.admin_view_cache_clear_error', ['error' => $e->getMessage()]));
        }
    }

    public function clearConfigCache()
    {
        try {
            Artisan::call('config:clear');
            session()->flash('cache_success', __('beach.admin_config_cache_cleared'));
        } catch (\Exception $e) {
            session()->flash('sync_error', __('beach.admin_config_cache_clear_error', ['error' => $e->getMessage()]));
        }
    }

    // ─── Database Operations ───
    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            session()->flash('sync_success', __('beach.admin_migrations_run', ['output' => nl2br(e($output))]));
        } catch (\Exception $e) {
            logger()->error('Admin migrations run failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_migrations_failed', ['error' => $e->getMessage()]));
        }
    }

    public function runSeeders()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();
            session()->flash('sync_success', __('beach.admin_seeders_run', ['output' => nl2br(e($output))]));
        } catch (\Exception $e) {
            logger()->error('Admin seeders run failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_seeders_failed', ['error' => $e->getMessage()]));
        }
    }

    // ─── Data Sync ───
    public function syncIpmaData()
    {
        try {
            FetchIpmaForecasts::dispatch();
            Setting::set('last_ipma_sync_attempt', now()->toIso8601String());
            session()->flash('sync_success', __('beach.admin_ipma_sync_started'));
        } catch (\Exception $e) {
            logger()->error('Ipma manual sync failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_ipma_sync_error', ['error' => $e->getMessage()]));
        }
    }

    public function syncIpmaDataSync()
    {
        try {
            $beaches = Beach::where('is_active', true)->get();
            foreach ($beaches as $beach) {
                FetchIpmaForecasts::dispatchSync($beach);
            }
            Setting::set('last_ipma_sync', now()->toIso8601String());
            session()->flash('sync_success', __('beach.admin_ipma_synced', ['count' => $beaches->count()]));
        } catch (\Exception $e) {
            logger()->error('Ipma manual sync sync failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_ipma_sync_failed', ['error' => $e->getMessage()]));
        }
    }

    public function syncWaterQualityData()
    {
        try {
            FetchInfoAguaData::dispatch();
            Setting::set('last_infoagua_sync_attempt', now()->toIso8601String());
            session()->flash('sync_success', __('beach.admin_infoagua_sync_started'));
        } catch (\Exception $e) {
            logger()->error('InfoAgua manual sync failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_infoagua_sync_error', ['error' => $e->getMessage()]));
        }
    }

    public function syncWaterQualityDataSync()
    {
        try {
            $beaches = Beach::where('is_active', true)->get();
            foreach ($beaches as $beach) {
                FetchInfoAguaData::dispatchSync($beach);
            }
            Setting::set('last_infoagua_sync', now()->toIso8601String());
            session()->flash('sync_success', __('beach.admin_infoagua_synced', ['count' => $beaches->count()]));
        } catch (\Exception $e) {
            logger()->error('InfoAgua manual sync sync failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_infoagua_sync_failed', ['error' => $e->getMessage()]));
        }
    }

    public function processQueue()
    {
        try {
            Artisan::call('queue:work', ['--stop-when-empty' => true]);
            $output = Artisan::output();
            session()->flash('sync_success', __('beach.admin_queue_processed', ['output' => nl2br(e($output))]));
        } catch (\Exception $e) {
            logger()->error('Admin queue:work failed: '.$e->getMessage());
            session()->flash('sync_error', __('beach.admin_queue_failed', ['error' => $e->getMessage()]));
        }
    }

    // ─── System ───
    public function getSystemInfo(): array
    {
        $driver = DB::connection()->getDriverName();

        $queueSize = 0;
        try {
            $queueSize = DB::table('jobs')->count();
        } catch (\Exception $e) {
            $queueSize = -1;
        }

        $failedJobs = 0;
        try {
            $failedJobs = DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            $failedJobs = -1;
        }

        return [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'queue_size' => $queueSize,
            'failed_jobs' => $failedJobs,
            'last_ipma_sync' => Setting::get('last_ipma_sync', __('beach.admin_never')),
            'last_infoagua_sync' => Setting::get('last_infoagua_sync', __('beach.admin_never')),
            'db_driver' => $driver,
        ];
    }

    // ─── Render ───
    public function render()
    {
        // Metrics cached for 60s — avoids re-running 8 queries on every Livewire action
        $metrics = Cache::remember('admin_dashboard_metrics', 60, function () {
            return [
                'totalUsers' => User::count(),
                'reportsToday' => FlagReport::where('reported_at', '>=', now()->startOfDay())->count(),
                'totalPredictions' => FlagPrediction::where('calculated_at', '>=', now()->subHours(24))->count(),
                'activeAlerts' => OfficialAlert::where('started_at', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
                    })->count(),
                'totalBeaches' => Beach::count(),
                'activeBeaches' => Beach::where('is_active', true)->count(),
                'suspendedUsers' => User::where('is_suspended', true)->count(),
                'adminUsers' => User::where('is_admin', true)->count(),
                'beachesWithStatus' => BeachCurrentStatus::count(),
                'flagDistribution' => BeachCurrentStatus::select('flag', DB::raw('count(*) as total'))
                    ->groupBy('flag')
                    ->pluck('total', 'flag')
                    ->toArray(),
            ];
        });

        $totalUsers = $metrics['totalUsers'];
        $reportsToday = $metrics['reportsToday'];
        $totalPredictions = $metrics['totalPredictions'];
        $activeAlerts = $metrics['activeAlerts'];
        $totalBeaches = $metrics['totalBeaches'];
        $activeBeaches = $metrics['activeBeaches'];
        $suspendedUsers = $metrics['suspendedUsers'];
        $adminUsers = $metrics['adminUsers'];
        $beachesWithStatus = $metrics['beachesWithStatus'];
        $flagDistribution = $metrics['flagDistribution'];

        // Tab-specific queries (only run when needed)
        $users = User::whereKey(0)->paginate(15);
        $adjustments = collect();
        $beaches = Beach::whereKey(0)->paginate(10, ['*'], 'beachesPage');
        $settings = collect();
        $systemInfo = [];

        if ($this->activeTab === 'utilizadores') {
            $usersQuery = User::query();
            if ($this->searchUser) {
                $usersQuery->where(function ($q) {
                    $q->where('username', 'like', '%'.$this->searchUser.'%')
                        ->orWhere('email', 'like', '%'.$this->searchUser.'%')
                        ->orWhere('name', 'like', '%'.$this->searchUser.'%');
                });
            }
            $users = $usersQuery->orderBy('score', 'desc')->paginate(15);
        }

        if (in_array($this->activeTab, ['utilizadores', 'visao-geral'])) {
            $adjustments = AdminScoreAdjustment::with(['admin', 'target'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        if ($this->activeTab === 'praias') {
            $beachQuery = Beach::query()->with(['currentStatus', 'translations']);
            if ($this->searchBeach) {
                $beachQuery->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->searchBeach.'%')
                        ->orWhere('municipality', 'like', '%'.$this->searchBeach.'%')
                        ->orWhere('region', 'like', '%'.$this->searchBeach.'%');
                });
            }
            if ($this->showInactiveOnly) {
                $beachQuery->where('is_active', false);
            }
            $beaches = $beachQuery->orderBy('is_active', 'desc')->orderBy('name')->paginate(10, ['*'], 'beachesPage');
        }

        if ($this->activeTab === 'configuracoes') {
            $settings = Setting::orderBy('key')->get();
        }

        if (in_array($this->activeTab, ['sistema', 'visao-geral'])) {
            $systemInfo = $this->getSystemInfo();
        }

        return view('livewire.admin.dashboard', [
            'totalUsers' => $totalUsers,
            'reportsToday' => $reportsToday,
            'totalPredictions' => $totalPredictions,
            'activeAlertsCount' => $activeAlerts,
            'totalBeaches' => $totalBeaches,
            'activeBeaches' => $activeBeaches,
            'suspendedUsers' => $suspendedUsers,
            'adminUsers' => $adminUsers,
            'beachesWithStatus' => $beachesWithStatus,
            'flagDistribution' => $flagDistribution,
            'usersList' => $users,
            'adjustmentsList' => $adjustments,
            'beachesList' => $beaches,
            'settingsList' => $settings,
            'systemInfo' => $systemInfo,
        ])->layout('components.layouts.app');
    }
}
