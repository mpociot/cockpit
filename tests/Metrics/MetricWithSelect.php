<?php namespace Mpociot\Tests;

use Mpociot\Cockpit\Metric;

class MetricWithSelect extends Metric
{
    /**
     * @var string
     */
    protected $name = "Users";

    /**
     * @var array
     */
    protected $allowedSubMetrics = [
        'one_chart' => ['PieChart'],
        'two_charts'   => ['LineChart', 'PieChart'],
        'per_time'   => ['LineChart'],
    ];

    /**
     * @var array
     */
    protected $allowedFilters = [
        'options' => [
            'type' => 'select',
            'name' => 'Username'
        ]
    ];

    public function calculateTwoCharts()
    {
        return $this->dataTable;
    }


    public function calculatePerTime()
    {
        return $this->dataTable;
    }

    /**
     * @param $query
     * @param $value
     *
     * @return mixed
     */
    protected function filterFromDate($query, $value)
    {
        // TODO: Implement filterFromDate() method.
    }

    /**
     * @param $query
     * @param $value
     *
     * @return mixed
     */
    protected function filterUntilDate($query, $value)
    {
        // TODO: Implement filterUntilDate() method.
    }

    public function getOptionsOptions()
    {
        return [ "1" => "Yes","0" => "No"];
    }
}