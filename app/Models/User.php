<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['firstName', 'lastName', 'email', 'password', 'image'];

    //Ein User kann mehrere Padlets haben
    public function padlets() : HasMany {
        return $this->hasMany(Padlet::class);
    }

    //Ein User kann mehrere Entries erstellen
    public function entries() : HasMany {
        return $this->hasMany(Entrie::class);
    }

    //Ein User kann mehrere Ratings erstellen
    public function ratings() : HasMany {
        return $this->hasMany(Rating::class);
    }

    //Ein User kann mehrere Kommentare erstellen
    public function comments() : HasMany {
        return $this->hasMany(Comment::class);
    }

    //Ein User kann mehrere Userrechte haben
    public function userrights() : HasMany {
        return $this->hasMany(Userright::class);
    }

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

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return ['user' => ['id' => $this->id]];
    }
}
