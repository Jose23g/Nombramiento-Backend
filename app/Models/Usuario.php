<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guarded = [];
    protected $table = 'usuarios';

    protected $password;
    protected $email;
    protected $hidden = [
        'contrasena',
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

    protected static function booted()
    {
        static::retrieved(function ($user) {
            $user->email = $user->correo;
            $user->password = $user->contrasena;
        });
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }
    public function findForPassport(string $correo): Usuario
    {
        return $this->where('correo', $correo)->first();
    }

    public function validateForPassportPasswordGrant(string $contrasena): bool
    {
        return Hash::check($contrasena, $this->contrasena);
    }

    public function aprobacionSolicitudCursos()
    {
        return $this->hasMany(AprobacionSolicitudCurso::class, 'encargado_id', 'id');
    }

    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'usuario_carreras', 'usuario_id', 'carrera_id');
    }

    public function usuarioCarreras()
    {
        return $this->hasMany(UsuarioCarrera::class, 'usuario_id', 'id');
    }

    public function solicitudCursos()
    {
        return $this->hasMany(SolicitudCurso::class, 'coordinador_id', 'id');
    }

    public function solicitudGrupos()
    {
        return $this->hasMany(SolicitudGrupo::class, 'profesor_id', 'id');
    }

    public function pSeis()
    {
        return $this->hasMany(PSeis::class, 'profesor_id', 'id');
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class, 'usuario_id', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }
    public function declaraciones()
    {
        return $this->hasMany(DeclaracionJurada::class, 'usuario_id', 'id');
    }
}
