<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'national_code',
        'role_code',
        'unit_code',
        'unit_name',
        'token',
        'token_expires_at',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function familyEnvHealth()
    {
        return $this->hasOne(FamilyEnvHealth::class);
    }

    public function familyEnvHealthForm()
    {
        return $this->hasMany(FamilyEnvHealthForm::class);
    }

    public function hyperTension()
    {
        return $this->hasOne(HyperTension::class,'user_id');
    }

    public function diabetic()
    {
        return $this->hasOne(Diabetic::class,'user_id');
    }
}
