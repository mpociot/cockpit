<?php namespace Mpociot\Tests;

use Mpociot\Cockpit\Metric;

class Users extends Metric
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
        'username' => [
            'type' => 'text',
            'name' => 'Username'
        ]
    ];

    public function calculateTwoCharts()
    {
        return $this->dataTable;
    }


    public function calculatePerTime( $filters = [] )
    {
        $this->queryFields = [
            \DB::raw('COUNT(*) AS `count`')
        ];

        $query = $this->applyFilters(\TestModel::query(), $filters);

        $query = $this->applyTimeGroup($query);


        $results = $query->get($this->queryFields);

        $results = $this->fillDateGaps($results, $filters);

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
        return $query;
    }

    /**
     * @param $query
     * @param $value
     *
     * @return mixed
     */
    protected function filterUntilDate($query, $value)
    {
        return $query;
    }

    public function filterUsername($query, $value)
    {
        return $query;
    }
}