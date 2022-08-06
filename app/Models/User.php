<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role_id',
        'is_verified',
        'provider_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        $this->belongsTo('App\Role');
    }

    public function adminInformation()
    {
        return $this->hasOne('App\AdminInformation');
    }

    public function customerInformation()
    {
        return $this->hasOne('App\CustomerInformation');
    }

    public function addresses()
    {
        return $this->hasMany('App\Address');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'favorite_categories', 'user_id', 'category_id');
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function orderes(){
        return $this->hasMany(Order::class);
    }
}
