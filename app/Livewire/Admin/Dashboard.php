<?php

namespace App\App\Livewire\Admin; // Wait, path is App\Livewire\Admin

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\FlagReport;
use App\Models\FlagPrediction;
use App\Models\OfficialAlert;
use App\Models\ScoreTransaction;
use App\Models\AdminScoreAdjustment;
use App\Models\AdCampaign;
use App\Models\Beach;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    // Search users
    public $searchUser = '';

    // Score adjustment form
    public $selectedUserId;
    public $selectedUser;
    public $adjustmentPoints;
    public $justification;

    // Campaign form
    public $clientName;
    public $campaignType = 'banner';
    public $campaignTitle;
    public $campaignLink;
    public $placementType = 'home';
    public $campaignStartsAt;
    public $campaignEndsAt;
    public $campaignBeachId;

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

        if (!Auth::check()) {
            $this->addError('adjust', 'Deves estar autenticado.');
            return;
        }

        $admin = Auth::user();
        $targetUser = $this->selectedUser;
        $prevPoints = $targetUser->score;
        $newPoints = (int) $this->adjustmentPoints;
        $difference = $newPoints - $prevPoints;

        DB::transaction(function () use ($admin, $targetUser, $prevPoints, $newPoints, $difference) {
            // Log the score transaction
            ScoreTransaction::create([
                'user_id' => $targetUser->id,
                'type' => 'admin_adjustment',
                'points' => $difference,
                'status' => 'confirmed',
                'description' => "Ajuste manual administrativo: " . $this->justification,
            ]);

            // Log detailed admin audit record
            AdminScoreAdjustment::create([
                'admin_user_id' => $admin->id,
                'target_user_id' => $targetUser->id,
                'previous_points' => $prevPoints,
                'new_points' => $newPoints,
                'difference' => $difference,
                'justification' => $this->justification,
            ]);

            // Update user
            $targetUser->score = $newPoints;
            $targetUser->save();
        });

        session()->flash('adjust_success', 'Pontuação de ' . $targetUser->username . ' ajustada para ' . $newPoints . '!');
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->justification = '';
    }

    public function createCampaign()
    {
        $this->validate([
            'clientName' => 'required|string|max:255',
            'campaignTitle' => 'required|string|max:255',
            'campaignLink' => 'required|url',
            'campaignStartsAt' => 'required|date',
            'campaignEndsAt' => 'required|date|after_or_equal:campaignStartsAt',
            'campaignBeachId' => 'nullable|exists:beaches,id',
        ]);

        AdCampaign::create([
            'client_name' => $this->clientName,
            'type' => $this->campaignType,
            'title' => $this->campaignTitle,
            'image_path' => 'ads/placeholder.jpg',
            'link' => $this->campaignLink,
            'placement_type' => $this->placementType,
            'beach_id' => $this->campaignBeachId,
            'starts_at' => $this->campaignStartsAt,
            'ends_at' => $this->campaignEndsAt,
            'is_active' => true,
        ]);

        session()->flash('campaign_success', 'Campanha publicitária agendada com sucesso!');
        $this->clientName = '';
        $this->campaignTitle = '';
        $this->campaignLink = '';
        $this->campaignStartsAt = null;
        $this->campaignEndsAt = null;
        $this->campaignBeachId = null;
    }

    public function toggleSuspension($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->is_suspended = !$user->is_suspended;
            $user->save();
            session()->flash('user_action', 'Estado de suspensão de ' . $user->username . ' alterado.');
        }
    }

    public function render()
    {
        // 1. Calculate General Metrics
        $totalUsers = User::count();
        $reportsToday = FlagReport::where('reported_at', '>=', now()->startOfDay())->count();
        $totalPredictions = FlagPrediction::where('calculated_at', '>=', now()->subHours(24))->count();
        $activeAlerts = OfficialAlert::where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->count();

        // 2. Query Users
        $usersQuery = User::query();
        if ($this->searchUser) {
            $usersQuery->where('username', 'like', '%' . $this->searchUser . '%')
                       ->orWhere('email', 'like', '%' . $this->searchUser . '%')
                       ->orWhere('name', 'like', '%' . $this->searchUser . '%');
        }
        $users = $usersQuery->orderBy('score', 'desc')->take(10)->get();

        // 3. Query recent audit logs
        $adjustments = AdminScoreAdjustment::with(['admin', 'target'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 4. Query beaches for campaign dropdown
        $beaches = Beach::all();

        return view('livewire.admin.dashboard', [
            'totalUsers' => $totalUsers,
            'reportsToday' => $reportsToday,
            'totalPredictions' => $totalPredictions,
            'activeAlertsCount' => $activeAlerts,
            'usersList' => $users,
            'adjustmentsList' => $adjustments,
            'beachesList' => $beaches,
        ])->layout('components.layouts.app');
    }
}
