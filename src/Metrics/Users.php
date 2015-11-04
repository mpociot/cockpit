<?php namespace App\Cockpit\Metrics;

use Config;
use DB;
use Mpociot\Cockpit\Metric;

/**
 * This is an example metric provided by Cockpit
 *
 * @package App\Cockpit\Metrics
 */
class Users extends Metric
{
    /**
     * This is the name, which will be used for the
     * modal, when selecting this metric.
     *
     * @var string
     */
    protected $name = "Users";

    /**
     * All available submetrics.
     * Each submetric can have multiple allowed chart types.
     *
     * @var array
     */
    protected $allowedSubMetrics = [
        'per_time' => ['LineChart'],
    ];

    /**
     * All allowed filters for this metric.
     *
     * @var array
     */
    protected $allowedFilters = [
        'username' => [
            'type' => 'text',
            'name' => 'Username'
        ]
    ];

    /**
     * Calculate everything, necessary for the "per_time" submetric.
     *
     * @param array $filters All (optional) filters
     *
     * @return \Khill\Lavacharts\Configs\DataTable
     */
    public function calculatePerTime($filters = [])
    {
        $this->queryFields = [
            DB::raw('COUNT(*) AS `count`')
        ];

        // Initial query
        $model = app(Config::get("auth.model"));

        // Apply filter methods to the query
        $query = $this->applyFilters($model->query(), $filters);

        /**
         * Apply time grouping to this query. This will automatically
         * add a "time" attribute to your query results
         */
        $query = $this->applyTimeGroup($query);

        // Retrieve all query fields
        $results = $query->get($this->queryFields);

        /**
         * Optional: Fill date gaps from SQL results
         */
        $results = $this->fillDateGaps($results, $filters);

        $this->dataTable
            ->addColumn($this->getColumnTypeForTimeGroup(), "Date")
            ->addNumberColumn("Num users");

        // Populate dataTable
        foreach ($results as $result) {
            $this->dataTable->addRow([
                $result->time,
                $result->count
            ]);

        }

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
}