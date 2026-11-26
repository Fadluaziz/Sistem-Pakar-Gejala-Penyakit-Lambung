<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di belakang tunnel/proxy (ngrok, Cloudflare, load balancer) aplikasi
        // menerima request sebagai HTTP dan skema aslinya dikirim lewat header
        // X-Forwarded-Proto. Tanpa trustProxies, Laravel mengira request-nya http
        // sehingga URL/redirect yang di-generate memakai http:// dan link https
        // (mis. domain ngrok) gagal dibuka.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
