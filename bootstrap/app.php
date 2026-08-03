<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SetLocale;
use Illuminate\Database\QueryException;
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
                    $refererPath = trim((string) parse_url($referer, PHP_URL_PATH), '/');
                    $guestAuthPaths = [
                        'login',
                        'register',
                        'forgot-password',
                        'reset-password',
                        'verify-email',
                        'confirm-password',
                    ];
                    $refererIsGuestAuthPath = collect($guestAuthPaths)->contains(
                        fn (string $path): bool => $refererPath === $path || Str::startsWith($refererPath, "{$path}/")
                    );

                    if (
                        $referer !== ''
                        && Str::startsWith($referer, $request->root())
                        && $referer !== $currentUrl
                        && ! $refererIsGuestAuthPath
                    ) {
                        return redirect()->to($referer)->with('error', $message);
                    }

                    return response()->view('errors.403', [], 403);
                }

                return null;
            }

            if ($exception instanceof QueryException && str_contains($exception->getMessage(), 'cannot be null')) {
                preg_match("/Column '([^']+)' cannot be null/", $exception->getMessage(), $matches);

                $field = $matches[1] ?? 'field';
                $label = __("validation.attributes.{$field}");
                $label = $label === "validation.attributes.{$field}" ? str($field)->replace('_', ' ')->title()->toString() : $label;
                $message = "حقل {$label} مطلوب، يرجى تعبئته قبل الحفظ.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                        'errors' => [
                            $field => [$message],
                        ],
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->withErrors([$field => $message])
                    ->with('error', $message);
            }

            return null;
        });
    })->create();
