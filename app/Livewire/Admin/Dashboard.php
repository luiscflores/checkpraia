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
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    // ─── Tab Navigation ───
    public $activeTab = 'visao-geral';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // ─── User Management ───
    public $searchUser = '';

    public $selectedUserId;

    public $selectedUser;

    public $adjustmentPoints;

    public $justification;

    public function selectUser($userId)
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
                'description' => 'Ajuste manual administrativo: '.$this->justification,
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

        session()->flash('adjust_success', 'Pontuação de '.$targetUser->username.' ajustada para '.$newPoints.'!');
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
            session()->flash('user_action', 'Estado de suspensão de '.$user->username.' alterado.');
        }
    }

    public function makeAdmin($userId)
    {
        $user = User::find($userId);
        if ($user && ! $user->is_admin) {
            $user->is_admin = true;
            $user->save();
            session()->flash('user_action', $user->username.' promovido a administrador.');
        }
    }

    public function removeAdmin($userId)
    {
        if ($userId === Auth::id()) {
            session()->flash('sync_error', 'Não podes remover os teus próprios privilégios de admin.');

            return;
        }
        $user = User::find($userId);
        if ($user && $user->is_admin) {
            $user->is_admin = false;
            $user->save();
            session()->flash('user_action', 'Privilégios de admin removidos de '.$user->username.'.');
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
            session()->flash('beach_action', $beach->name.' '.($beach->is_active ? 'ativada' : 'desativada').'.');
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
        session()->flash('beach_action', 'Votos de hoje em '.$beach->name.' foram cancelados.');
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
            'reason' => 'Sobreposição manual pelo administrador.',
        ]);
        $status->save();

        $this->overrideBeachId = null;
        session()->flash('beach_action', 'Bandeira de '.$beach->name.' sobreposta para '.$this->overrideFlag.'.');
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

        session()->flash('settings_success', 'Definição "'.$this->newSettingKey.'" criada.');
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
            session()->flash('settings_success', 'Definição "'.$this->editingSetting->key.'" atualizada.');
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
            session()->flash('settings_success', 'Definição "'.$setting->key.'" eliminada.');
        }
    }

    // ─── Cache Management ───
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            session()->flash('cache_success', 'Cache de aplicação limpa com sucesso.');
        } catch (\Exception $e) {
            session()->flash('sync_error', 'Erro ao limpar cache: '.$e->getMessage());
        }
    }

    public function clearAllCache()
    {
        try {
            Artisan::call('optimize:clear');
            $output = Artisan::output();
            session()->flash('cache_success', 'Cache total limpa: '.nl2br(e($output)));
        } catch (\Exception $e) {
            session()->flash('sync_error', 'Erro ao limpar cache total: '.$e->getMessage());
        }
    }

    public function clearViewCache()
    {
        try {
            Artisan::call('view:clear');
            session()->flash('cache_success', 'Cache de views limpa com sucesso.');
        } catch (\Exception $e) {
            session()->flash('sync_error', 'Erro ao limpar cache de views: '.$e->getMessage());
        }
    }

    public function clearConfigCache()
    {
        try {
            Artisan::call('config:clear');
            session()->flash('cache_success', 'Cache de configuração limpa com sucesso.');
        } catch (\Exception $e) {
            session()->flash('sync_error', 'Erro ao limpar cache de configuração: '.$e->getMessage());
        }
    }

    // ─── Database Operations ───
    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            session()->flash('sync_success', 'Migrações executadas: '.nl2br(e($output)));
        } catch (\Exception $e) {
            logger()->error('Admin migrations run failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao executar migrações: '.$e->getMessage());
        }
    }

    public function runSeeders()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();
            session()->flash('sync_success', 'Seeders executados: '.nl2br(e($output)));
        } catch (\Exception $e) {
            logger()->error('Admin seeders run failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao executar seeders: '.$e->getMessage());
        }
    }

    // ─── Data Sync ───
    public function syncIpmaData()
    {
        try {
            FetchIpmaForecasts::dispatch();
            Setting::set('last_ipma_sync_attempt', now()->toIso8601String());
            session()->flash('sync_success', 'Sincronização IPMA/Open-Meteo iniciada em segundo plano.');
        } catch (\Exception $e) {
            logger()->error('Ipma manual sync failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao iniciar sincronização IPMA: '.$e->getMessage());
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
            session()->flash('sync_success', 'Previsões IPMA para '.$beaches->count().' praias sincronizadas.');
        } catch (\Exception $e) {
            logger()->error('Ipma manual sync sync failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao sincronizar IPMA: '.$e->getMessage());
        }
    }

    public function syncWaterQualityData()
    {
        try {
            FetchInfoAguaData::dispatch();
            Setting::set('last_infoagua_sync_attempt', now()->toIso8601String());
            session()->flash('sync_success', 'Sincronização InfoÁgua iniciada em segundo plano.');
        } catch (\Exception $e) {
            logger()->error('InfoAgua manual sync failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao iniciar sincronização InfoÁgua: '.$e->getMessage());
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
            session()->flash('sync_success', 'Dados InfoÁgua para '.$beaches->count().' praias sincronizados.');
        } catch (\Exception $e) {
            logger()->error('InfoAgua manual sync sync failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao sincronizar InfoÁgua: '.$e->getMessage());
        }
    }

    public function processQueue()
    {
        try {
            Artisan::call('queue:work', ['--stop-when-empty' => true]);
            $output = Artisan::output();
            session()->flash('sync_success', 'Fila processada: '.nl2br(e($output)));
        } catch (\Exception $e) {
            logger()->error('Admin queue:work failed: '.$e->getMessage());
            session()->flash('sync_error', 'Falha ao processar fila: '.$e->getMessage());
        }
    }

    // ─── System ───
    public function getSystemInfo(): array
    {
        $queueSize = 0;
        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                $queueSize = DB::table('jobs')->count();
            }
        } catch (\Exception $e) {
            $queueSize = -1;
        }

        $failedJobs = 0;
        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                $failedJobs = DB::table('failed_jobs')->count();
            }
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
            'last_ipma_sync' => Setting::get('last_ipma_sync', 'Nunca'),
            'last_infoagua_sync' => Setting::get('last_infoagua_sync', 'Nunca'),
            'db_driver' => DB::connection()->getDriverName(),
        ];
    }

    // ─── Render ───
    public function render()
    {
        // Metrics (cheap — always compute for visao-geral tab)
        $totalUsers = User::count();
        $reportsToday = FlagReport::where('reported_at', '>=', now()->startOfDay())->count();
        $totalPredictions = FlagPrediction::where('calculated_at', '>=', now()->subHours(24))->count();
        $activeAlerts = OfficialAlert::where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->count();
        $totalBeaches = Beach::count();
        $activeBeaches = Beach::where('is_active', true)->count();
        $suspendedUsers = User::where('is_suspended', true)->count();
        $adminUsers = User::where('is_admin', true)->count();
        $beachesWithStatus = BeachCurrentStatus::count();
        $flagDistribution = BeachCurrentStatus::select('flag', DB::raw('count(*) as total'))
            ->groupBy('flag')
            ->pluck('total', 'flag')
            ->toArray();

        // Tab-specific queries (only run when needed)
        $users = User::whereKey(0)->paginate(15);
        $adjustments = collect();
        $beaches = Beach::whereKey(0)->paginate(10, ['*'], 'beachesPage');
        $settings = collect();
        $systemInfo = [];

        if (in_array($this->activeTab, ['utilizadores', 'visao-geral'])) {
            $usersQuery = User::query();
            if ($this->searchUser) {
                $usersQuery->where(function ($q) {
                    $q->where('username', 'like', '%'.$this->searchUser.'%')
                        ->orWhere('email', 'like', '%'.$this->searchUser.'%')
                        ->orWhere('name', 'like', '%'.$this->searchUser.'%');
                });
            }
            $users = $usersQuery->orderBy('score', 'desc')->paginate(15);

            $adjustments = AdminScoreAdjustment::with(['admin', 'target'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        if (in_array($this->activeTab, ['praias', 'visao-geral'])) {
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

        if (in_array($this->activeTab, ['configuracoes', 'visao-geral'])) {
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
