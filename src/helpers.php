<?php

if(!function_exists('tab_manager')) {
    function tab_manager($key = null){
        $instance = app('tab-manager');
        if (is_null($key)) {
            return $instance;
        }

        return $instance->get($key);
    }
}
