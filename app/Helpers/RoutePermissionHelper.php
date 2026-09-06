<?php
namespace App\Helpers;


use Illuminate\Support\Facades\Route;
class RoutePermissionHelper
{

    // route groups excluded from permissions
    protected static array $excludedGroups = [
        'sanctum',
        'ignition',
        'login',
        'logout',
        'register',
        'password',
        'passwordconfirm',
        'verification',
        'forgotpassword',
        'userprofile',
        'userpassword',
    ];

    public static function getAllRoutes()
    {
        $routes = collect(Route::getRoutes())
                    ->map(fn($route) => $route->getName())
                    ->filter()
                    ->reject(fn($name) => in_array($name, static::$excludedGroups))
                    ->groupBy(function ($name) {
                        return explode('.', $name)[0];
                    })
                    ->reject(function ($value, $key) {
                        return in_array($key, static::$excludedGroups);
                    });

        return $routes;
    }

}
