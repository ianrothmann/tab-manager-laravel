<?php

namespace StianScholtz\TabManager\Middleware;

use Closure;
use Illuminate\Http\Request;
use StianScholtz\TabManager\Facades\TabManager;

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

        $response = $next($request);

        //        $response->headers->set('Server-Timing', 'tab_id;desc="' . TabManager::getInstance()->current() . '";');

        return $response;
    }
}
