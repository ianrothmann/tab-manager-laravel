<?php

namespace Eawardie\TabManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Eawardie\TabManager\Services\TabManagerService
 */
class TabManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tab-manager';
    }
}
