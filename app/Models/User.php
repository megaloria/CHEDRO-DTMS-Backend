<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'role_id',
        'is_first_login'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role_id' => 'integer',
        'is_first_login' => 'boolean'
    ];

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.'.$this->id;
    }

    public function routeNotificationForMail(Notification $notification): array|string
    {
        // Return email address and name...
        return [$this->profile->email => $this->profile->name];
    }

    public function profile() {
        return $this->hasOne('App\Models\Profile', 'id', 'id');
    }

    public function getUnreadNotificationsCountAttribute () {
        return $this->unreadNotifications()->count();
    }

    public function role() {
        return $this->belongsTo('App\Models\Role');
    }
}
