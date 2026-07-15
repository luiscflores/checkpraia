<?php

namespace App\Livewire\Account;

use App\Livewire\Actions\Logout;
use App\Models\FlagReport;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Profile extends Component
{
    public $editName;

    public $editUsername;

    public $editLocale;

    public function mount()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $this->editName = $user->name;
        $this->editUsername = $user->username;
        $this->editLocale = session('locale', app()->getLocale());
        if (Schema::hasColumn('users', 'locale') && $user->locale) {
            $this->editLocale = $user->locale;
        }
    }

    public function updateProfile()
    {
        if (! Auth::check()) {
            $this->addError('profile', 'Sessão expirada.');

            return;
        }
        $user = Auth::user();
        $this->validate([
            'editName' => 'required|string|max:255',
            'editUsername' => 'required|string|alpha_dash|unique:users,username,'.$user->id,
            'editLocale' => 'nullable|string|in:'.implode(',', config('locales.supported', ['pt', 'en', 'es', 'fr'])),
        ]);

        $user->update([
            'name' => $this->editName,
            'username' => $this->editUsername,
            'locale' => $this->editLocale,
        ]);

        if ($this->editLocale) {
            session(['locale' => $this->editLocale]);
            app()->setLocale($this->editLocale);
        }

        session()->flash('profile_success', 'Perfil atualizado com sucesso.');
    }

    public function removeFavorite($beachId)
    {
        if (! Auth::check()) {
            return;
        }
        $user = Auth::user();
        $user->favorites()->detach($beachId);
        session()->flash('favorite_removed', __('common.favorite_removed'));
    }

    public function logout(Logout $logout)
    {
        $logout();

        return redirect()->route('home');
    }

    public function deleteAccount()
    {
        if (! Auth::check()) {
            return;
        }
        $user = Auth::user();

        DB::transaction(function () use ($user) {
            $user->reports()->delete();
            $user->scoreTransactions()->delete();
            $user->referrals()->delete();
            $user->favorites()->detach();
            $user->delete();
        });

        app(Logout::class)();

        return redirect()->route('home');
    }

    public function render()
    {
        $user = Auth::user();

        // Fetch user visit reports
        $locale = app()->getLocale();
        $reports = FlagReport::select(['id', 'beach_id', 'flag', 'status', 'distance_to_beach', 'gps_accuracy', 'reported_at', 'resolved_at'])
            ->with(['beach' => function ($q) use ($locale) {
                $q->select(['id', 'name', 'slug', 'latitude', 'longitude', 'region', 'municipality'])
                    ->with(['translations' => fn ($t) => $t->where('locale', $locale)->select('beach_id', 'name')]);
            }])
            ->where('user_id', $user->id)
            ->orderBy('reported_at', 'desc')
            ->take(50)
            ->get();

        // Fetch user favorites
        $favorites = $user->favorites()
            ->select(['id', 'name', 'slug', 'latitude', 'longitude', 'region', 'municipality', 'blue_flag', 'accessible', 'is_active', 'is_supervised', 'season_start', 'season_end', 'lifeguard_start', 'lifeguard_end'])
            ->with(['currentStatus:beach_id,flag', 'translations' => fn ($q) => $q->where('locale', $locale)->select('beach_id', 'name')])
            ->get();

        // Fetch referral stats (single query)
        $referralStats = Referral::where('referrer_user_id', $user->id)
            ->selectRaw('COUNT(*) as total, SUM(status = ?) as qualified', ['qualified'])
            ->first();
        $totalInvited = $referralStats->total;
        $totalQualifiedInvited = (int) $referralStats->qualified;
        $referralProgress = $totalQualifiedInvited % 5;

        return view('livewire.account.profile', [
            'reports' => $reports,
            'favorites' => $favorites,
            'referralProgress' => $referralProgress,
            'totalInvited' => $totalInvited,
            'totalQualifiedInvited' => $totalQualifiedInvited,
        ])->layout('components.layouts.app');
    }
}
