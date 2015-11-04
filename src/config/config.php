<?php

/**
 * This file is part of Cockpit
 *
 * @license MIT
 * @package Mpociot\Cockpit
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Metrics namespace
    |--------------------------------------------------------------------------
    |
    | This is the namespace used for autoloading your
    | user defined metrics.
    |
    */
    'metrics_namespace' => "App\\Cockpit\\Metrics\\",

    /*
    |--------------------------------------------------------------------------
    | Metrics path
    |--------------------------------------------------------------------------
    |
    | Similar to the metrics namespace, this value is
    | used to list all available metrics.
    |
    */
    'metrics_path' => app_path() . "/Cockpit/Metrics/"
];