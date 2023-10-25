<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $timestamps = false;

    protected $table = 'usuarios';
    protected $password = 'contrasena';

    protected $guarded = [];

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

    public function aprobacionSolicitudCursos(): HasMany
    {
        return $this->hasMany(AprobacionSolicitudCurso::class, 'encargado_id', 'id');
    }

    public function carreras(): BelongsToMany
    {
        return $this->belongsToMany(Carrera::class, 'usuario_carreras', 'usuario_id', 'carrera_id');
    }

    public function usuarioCarreras(): HasMany
    {
        return $this->hasMany(UsuarioCarrera::class, 'usuario_id', 'id');
    }

    public function solicitudCursos(): HasMany
    {
        return $this->hasMany(SolicitudCurso::class, 'coordinador_id', 'id');
    }

    public function pSeis(): HasMany
    {
        return $this->hasMany(PSeis::class, 'profesor_id', 'id');
    }

    public function trabajos(): HasMany
    {
        return $this->hasMany(Trabajo::class, 'usuario_id', 'id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongTo(Persona::class, 'persona_id', 'id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }
}
