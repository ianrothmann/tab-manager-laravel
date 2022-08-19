<?php

namespace StianScholtz\TabManager\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckForTabId
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
        $response = $next($request);

        if(auth()->check()) {
            $tabId = \StianScholtz\TabManager\Facades\TabManager::check();
        }
//        $response->headers->set('Server-Timing', 'tab_id;desc="' . TabManager::getInstance()->current() . '";');

        return $response;
    }
}
