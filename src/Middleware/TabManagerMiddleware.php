<?php

namespace Eawardie\TabManager\Middleware;

use Closure;
use Illuminate\Http\Request;
use Eawardie\TabManager\Facades\TabManager;

class TabManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tabId = TabManager::check();

        return $next($request);
    }
}
