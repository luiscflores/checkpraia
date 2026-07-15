<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            logger()->error('Google OAuth failed: '.$e->getMessage());

            return redirect()->route('login')->withErrors(['google' => 'Authentication with Google failed. Please try again.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                $baseUsername = Str::slug($googleUser->getName(), '_');
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername.$counter;
                    $counter++;
                }

                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'username' => $username,
                    'password' => Str::random(24),
                    'referral_code' => strtoupper(Str::random(8)),
                    'score' => 0,
                ]);
            }
        }

        if ($user->is_suspended) {
            Auth::logout();

            return redirect()->route('home')->withErrors(['account' => 'A tua conta está suspensa.']);
        }

        Auth::login($user);

        return redirect()->intended(route('home'));
    }
}
