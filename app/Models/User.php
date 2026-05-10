<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;
    protected $guard_name = 'api';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'code',
        'username',
        'email',
        'photo_url',
        'academic_cycle',
        'hours_of_practice',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Obtiene las reclamaciones personalizadas para el JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'last_name'          => $this->last_name,
            'username'           => $this->username,
            'email'              => $this->email,
            'code'               => $this->code,
            'photo_url'          => $this->photo_url,
            'academic_cycle'     => $this->academic_cycle,
            'hours_of_practice'  => $this->hours_of_practice,
            'email_verified'     => $this->hasVerifiedEmail(),
            'created_at'         => $this->created_at,
            'roles'              => $this->roles->pluck('name'),
            'permissions'        => $this->getPermissionsViaRoles()->pluck('name'),
        ];
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Storage::url($value) : null,
        );
    }

    // Usa nuestra notificación personalizada en lugar de la de Laravel
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function practices()
    {
        return $this->hasMany(Practice::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class); // Un usuario puede hacer muchas visitas
    }
}
