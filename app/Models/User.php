<?php

namespace App;

use App\AccessToken;
use App\RefreshToken;
use App\LoginToken;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * The refresh token which associated with the current user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function refreshToken() {
        return $this->hasOne(RefreshToken::class);
    }

    /**
     * The access token associated with the current user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accessToken() {
        return $this->hasOne(AccessToken::class);
    }

    /**
     * The login token associated with the current user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function loginToken() {
        return $this->hasOne(LoginToken::class);
    }
}
