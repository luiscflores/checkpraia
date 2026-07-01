<?php

namespace App\Livewire\Account;

use Livewire\Component;
use App\Models\User;
use App\Models\FlagReport;
use App\Models\Referral;
use App\Models\ScoreTransaction;
use App\Models\Beach;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;

class Profile extends Component
{
    public $editName;
    public $editUsername;

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $this->editName = $user->name;
        $this->editUsername = $user->username;
    }

    public function updateProfile()
    {
        if (!Auth::check()) { $this->addError('profile', 'Sessão expirada.'); return; }
        $user = Auth::user();
        $this->validate([
            'editName' => 'required|string|max:255',
            'editUsername' => 'required|string|alpha_dash|unique:users,username,' . $user->id,
        ]);

        $user->update([
            'name' => $this->editName,
            'username' => $this->editUsername,
        ]);

        session()->flash('profile_success', 'Perfil atualizado com sucesso.');
    }

    public function removeFavorite($beachId)
    {
        if (!Auth::check()) { return; }
        $user = Auth::user();
        $user->favorites()->detach($beachId);
        session()->flash('favorite_removed', 'Praia removida dos favoritos.');
    }

    public function logout(Logout $logout)
    {
        $logout();
        return redirect()->route('home');
    }

    public function deleteAccount()
    {
        if (!Auth::check()) { return; }
        $user = Auth::user();
        $user->reports()->delete();
        $user->scoreTransactions()->delete();
        $user->referrals()->delete();
        $user->favorites()->detach();
        Auth::logout();
        $user->delete();
        return redirect()->route('home');
    }

    public function render()
    {
        $user = Auth::user();

        // Fetch score transactions
        $transactions = ScoreTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch user visit reports
        $reports = FlagReport::with('beach')
            ->where('user_id', $user->id)
            ->orderBy('reported_at', 'desc')
            ->get();

        // Fetch user favorites
        $favorites = $user->favorites()->with('currentStatus')->get();

        // Fetch referral invitations counts
        $referralProgress = Referral::where('referrer_user_id', $user->id)
            ->where('status', 'qualified')
            ->count() % 5;

        $totalInvited = Referral::where('referrer_user_id', $user->id)->count();
        $totalQualifiedInvited = Referral::where('referrer_user_id', $user->id)->where('status', 'qualified')->count();

        return view('livewire.account.profile', [
            'transactions' => $transactions,
            'reports' => $reports,
            'favorites' => $favorites,
            'referralProgress' => $referralProgress,
            'totalInvited' => $totalInvited,
            'totalQualifiedInvited' => $totalQualifiedInvited,
        ])->layout('components.layouts.app');
    }
}
