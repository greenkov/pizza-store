<?php

namespace App\Services\Order\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HandleOrderControllerExceptionsMiddleware
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if ($response->getStatusCode() < 500 || ! isset($response->exception)) {
            return $response;
        }

        if (app()->hasDebugModeEnabled()) {
            return $response;
        }

        Inertia::flash('toast', [
            'type' => 'error',
            'message' => __('Something went wrong. Please try again.'),
        ]);

        return back();
    }
}
