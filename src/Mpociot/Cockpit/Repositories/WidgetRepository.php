<?php

namespace Mpociot\Cockpit\Repositories;

use Input;
use Mpociot\Cockpit\Widget;
use Mpociot\Cockpit\Metric;
use InvalidArgumentException;
use UnexpectedValueException;
use Illuminate\Support\Facades\Config;

class WidgetRepository
{

    /**
     * @return array
     */
    public function getAvailableMetrics()
    {
        $directory      = Config::get("cockpit.metrics_path", app_path() . "/Cockpit/Metrics/");
        $files          = glob($directory . "*.php");
        $allowedMetrics = [];

        foreach ($files as $file) {
            $metricClass = basename($file, ".php");
            $class       = Config::get("cockpit.metrics_namespace",
                    "App\\Cockpit\\Metrics\\") . studly_case($metricClass);

            try {
                $metric = app($class);
                $result = $metric->getName();
                $key    = $metric->getKey();

                if (!is_string($result)) {
                    throw new UnexpectedValueException("Expected a string, but received " . gettype($result));
                }

                if ($metric->active) {
                    $subMetrics                                     = $this->getSubMetrics($metric);
                    $allowedFilters                                 = $this->getFilters($metric);
                    $allowedMetrics[ $metricClass ][ "key" ]        = $key;
                    $allowedMetrics[ $metricClass ][ "submetrics" ] = $subMetrics;
                    $allowedMetrics[ $metricClass ][ "filters" ]    = $allowedFilters;
                }
            } catch (\ReflectionException $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        return $allowedMetrics;
    }

    /**
     * @param Widget $widget
     *
     * @return Widget
     */
    public function saveWidget(Widget $widget)
    {
        $widget->filters   = Input::get("filter", []);
        $widget->name      = Input::get("name", "");
        $widget->charttype = Input::get("charttype", "");
        $widget->metric    = Input::get("metric", "");
        $widget->submetric = Input::get("submetric", "");
        $widget->timegroup = Input::get("timegroup", "");

        if (!$widget->exists) {
            $widget->col    = 0;
            $widget->row    = 0;
            $widget->size_x = Input::get("size_x", "");
            $widget->size_y = Input::get("size_y", "");
        }

        $widget->save();

        return $widget;
    }

    /**
     * @param Metric $metric
     *
     * @throws UnexpectedValueException
     * @return array
     */
    private function getSubMetrics(Metric $metric)
    {
        $result = $metric->getAllowedSubMetrics();
        if (!is_array($result)) {
            throw new UnexpectedValueException("Expected an array, but received " . gettype($result));
        }

        return $result;
    }


    /**
     * @param Metric $metric
     *
     * @throws UnexpectedValueException
     * @return array
     */
    private function getFilters(Metric $metric)
    {
        $result = $metric->getAllowedFilters();
        if (!is_array($result)) {
            throw new UnexpectedValueException("Expected an array, but received " . gettype($result) . " for metric " . get_class($metric));
        }

        return $result;
    }

}