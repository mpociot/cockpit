<?php namespace Mpociot\Cockpit;

use Illuminate\Support\Facades\Config;
use ReflectionException;
use InvalidArgumentException;
use UnexpectedValueException;
use Khill\Lavacharts\Lavacharts;
use Khill\Lavacharts\Configs\DataTable;

/**
 * Class MetricHandler
 * @package Mpociot\Cockpit
 */
class MetricHandler
{

    /**
     * @var Filter
     */
    protected $globalFilter;

    /**
     * @param Filter $filter
     */
    public function __construct(Filter $filter)
    {
        $this->globalFilter = $filter;
    }

    /**
     * @param        $widget
     * @param array  $filters
     * @param string $timeGroup
     *
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function renderChart($widget, $filters = [], $timeGroup = "")
    {
        try {
            $chartType = $widget->charttype;

            /** @var \Mpociot\Cockpit\Metric $metric */
            $metric = app( Config::get("cockpit.metrics_namespace", "App\\Cockpit\\Metrics\\") . $widget->metric);
            $metric->setTimeGroup($timeGroup);
            $metric->setChartType($chartType);

            // Get metric results
            $result = $metric->getValues($widget->submetric, array_merge($this->globalFilter->toArray(), $filters));
            // Get metric title
            $title  = $metric->getTitle();

            if (!( $result instanceof DataTable )) {
                throw new UnexpectedValueException('Expected a datatable, but received ' . gettype($result));
            }

            // Create the Lavacharts Chart instance
            $charts = new Lavacharts();
            $charts->jsFactory->coreJsRendered(true);
            $chartName = "chart_" . $widget->getKey();

            $chart = $charts->{$chartType}($chartName);
            $chart->title($title);
            $chart->datatable($result);

            // Render the Chart to HTML
            return $charts->render($chartType, $chartName, $chartName, true);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }


}