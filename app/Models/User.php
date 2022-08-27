<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
//use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    //use TwoFactorAuthenticatable;
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'password',
        'profile_photo_path',
        'is_verified',
        'fcm_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        "bvn",
        "nin",
        "fcm_token",
        "bvn_verified",
        "nin_verified",
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }

    public function setFirstnameAttribute($firstname)
    {
        $this->attributes['firstname'] = ucwords(strtolower($firstname));
    }

    public function setLastnameAttribute($lastname)
    {
        $this->attributes['lastname'] = ucwords(strtolower($lastname));
    }

    /*public function getReferralCodeAttribute($lastname)
    {
        return $this->referralCode()->pluck('code')[0] ?? null;
    }*/

    public function referralCode()
    {
        return $this->hasOne(ReferralCode::class);
    } 
}
