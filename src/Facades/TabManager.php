<?php

namespace StianScholtz\TabManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \StianScholtz\TabManager\Services\TabManager
 */
class TabManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tab-manager';
    }
}
