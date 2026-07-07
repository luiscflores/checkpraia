<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'username', 'google_id', 'avatar', 'score', 'confirmations_count', 'accepted_confirmations_count', 'penalized_confirmations_count', 'is_suspended', 'is_admin', 'referral_code', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function reports()
    {
        return $this->hasMany(FlagReport::class);
    }

    public function scoreTransactions()
    {
        return $this->hasMany(ScoreTransaction::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_user_id');
    }

    public function referral()
    {
        return $this->hasOne(Referral::class, 'invited_user_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Beach::class, 'favorites', 'user_id', 'beach_id')->withTimestamps();
    }
}
