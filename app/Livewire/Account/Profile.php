<?php

namespace App\App\Livewire\Account; // Wait, app path is App\Livewire\Account

namespace App\Livewire\Account;

use Livewire\Component;
use App\Models\User;
use App\Models\FlagReport;
use App\Models\Referral;
use App\Models\ScoreTransaction;
use App\Models\Beach;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Profile extends Component
{
    // Auth Forms properties
    public $isRegister = false;
    public $name;
    public $email;
    public $username;
    public $password;
    public $referralCodeInput;

    // Login properties
    public $loginEmail;
    public $loginPassword;

    // Profile Settings edit
    public $editName;
    public $editUsername;

    public function mount()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->editName = $user->name;
            $this->editUsername = $user->username;
        }
    }

    public function login()
    {
        $this->validate([
            'loginEmail' => 'required|email',
            'loginPassword' => 'required',
        ]);

        if (Auth::attempt(['email' => $this->loginEmail, 'password' => $this->loginPassword])) {
            $user = Auth::user();
            $this->editName = $user->name;
            $this->editUsername = $user->username;
            session()->flash('auth_success', 'Sessão iniciada com sucesso!');
        } else {
            $this->addError('login', 'As credenciais fornecidas estão incorretas.');
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|alpha_dash|unique:users,username|min:3',
            'password' => 'required|string|min:6',
            'referralCodeInput' => 'nullable|exists:users,referral_code',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'password' => Hash::make($this->password),
            'referral_code' => strtoupper(Str::random(8)),
            'score' => 0,
        ]);

        // Process referral invitation if entered
        if ($this->referralCodeInput) {
            $referrer = User::where('referral_code', strtoupper($this->referralCodeInput))->first();
            if ($referrer) {
                Referral::create([
                    'referrer_user_id' => $referrer->id,
                    'invited_user_id' => $user->id,
                    'code' => strtoupper($this->referralCodeInput),
                    'status' => 'pending',
                ]);
            }
        }

        Auth::login($user);
        $this->editName = $user->name;
        $this->editUsername = $user->username;
        session()->flash('auth_success', 'Conta criada com sucesso!');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->to('/' . app()->getLocale() . '/area-pessoal');
    }

    public function updateProfile()
    {
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
        $user = Auth::user();
        $user->favorites()->detach($beachId);
        session()->flash('favorite_removed', 'Praia removida dos favoritos.');
    }

    public function deleteAccount()
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        return redirect()->to('/' . app()->getLocale());
    }

    public function render()
    {
        if (!Auth::check()) {
            return view('livewire.account.profile-auth')->layout('components.layouts.app');
        }

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
