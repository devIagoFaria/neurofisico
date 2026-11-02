<?php
use App\Http\Middleware\XSS;
use App\Http\Middleware\SetLanguage;
use App\Http\Middleware\SetPdfLanguage;
use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\CheckImpersonateUser;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'xss' => XSS::class,
            'setLanguage' => SetLanguage::class,
            'role' => RoleMiddleware::class,
            'checkUserStatus' => CheckUserStatus::class,
            'checkImpersonateUser' => CheckImpersonateUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
