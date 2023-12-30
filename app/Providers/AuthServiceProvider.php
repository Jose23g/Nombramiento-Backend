<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //Passport::routes();

        Passport::tokensCan([
            'Coordinador' => 'Tiene acceso intermedio en la aplicacion',
            'Profesor' => 'Tiene acceso limitado a la aplicacion',
            'Docencia' => 'Tiene acceso limitado a la aplicacion',
            'Director_de_Departamento' => 'Tiene acceso limitado a la aplicacion',
        ]);
        Passport::tokensExpireIn(now()->addMinutes(3));
        Passport::refreshTokensExpireIn(now()->addDay());
        Passport::personalAccessTokensExpireIn(now()->addMinute());

        Passport::setDefaultScope(
            [
                'Profesor',
            ]
        );
    }
}
