<?php

Route::get( '/cockpit', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@index' ) );
Route::get( '/cockpit/api/metrics', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@getMetrics' ) );
Route::post( '/cockpit/api', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@create' ) );
Route::get( '/cockpit/api/{widget_id}', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@show' ) );
Route::put( '/cockpit/api/settings', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@saveSettings' ) );
Route::put( '/cockpit/api/{widget_id}', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@save' ) );
Route::delete( '/cockpit/api/{widget_id}', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@delete' ) );
Route::post( '/cockpit/api/savePosition', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@savePosition' ) );
Route::get( '/cockpit/api/filters/{metric}', array('uses' => 'Mpociot\Cockpit\Controller\CockpitController@getFiltersForMetric' ) );