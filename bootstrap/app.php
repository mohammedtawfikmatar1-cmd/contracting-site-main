<?php

/**
 * نقطة إعداد تطبيق Laravel 11 (بديل ملف kernel.php القديم في إصدارات أقدم).
 *
 * - withRouting: أين ملفات المسارات (web.php، console.php)
 * - withMiddleware: وسائط HTTP عامة (هنا معطّل تبديل اللغة مؤقتًا)
 * - withExceptions: تخصيص صفحات الأخطاء لاحقًا إن لزم
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// use App\Http\Middleware\SetLocale;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // تعطيل نظام الغات لحين اكماله في تحديث ءاخر تشغيل وسيط تبديل اللغة

        // $middleware->add(SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
