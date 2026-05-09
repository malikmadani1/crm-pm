<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->web(append: [
            SetLocale::class,
        ]);
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'locale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request): ?Response {
            Config::set('app.debug', true);

            if ($exception instanceof HttpExceptionInterface) {
                if ($exception->getStatusCode() === 403) {
                    $message = __('You do not have permission to access this page.');
                    $referer = (string) $request->headers->get('referer', '');
                    $currentUrl = $request->fullUrl();

                    if (
                        $referer !== ''
                        && Str::startsWith($referer, $request->root())
                        && $referer !== $currentUrl
                    ) {
                        return redirect()->to($referer)->with('error', $message);
                    }

                    return response()->view('errors.403', [], 403);
                }

                return null;
            }

            return null;
        });
    })->create();
