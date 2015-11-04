<?php namespace Mpociot\Cockpit;

use BadMethodCallException;
use Illuminate\Support\Facades\DB;
use Khill\Lavacharts\Configs\DataTable;
use Illuminate\Database\Eloquent\Collection;

abstract class Metric
{
    /**
     * @var array
     */
    protected $allowedSubMetrics = [];

    /**
     * @var array
     */
    protected $allowedFilters = [];

    /**
     * @var array
     */
    protected $queryFields = [];

    /**
     * Class name for this metric
     * @var string
     */
    protected $name = "";

    /**
     * Language key to use for this metric
     * @var string
     */
    protected $key = "";

    /**
     * @var \Khill\Lavacharts\Configs\DataTable
     */
    protected $dataTable;

    /**
     * @var string
     */
    protected $chartType;

    /**
     * @var string
     */
    protected $timeGroup;

    const TIME_GROUP_DAY = "DAY";
    const TIME_GROUP_WEEK = "WEEK";
    const TIME_GROUP_MONTH = "MONTH";
    const TIME_GROUP_YEAR = "YEAR";

    /**
     * @var bool
     */
    public $active = true;

    /**
     * @var array
     */
    protected $allowedTimeGroup = [
        self::TIME_GROUP_DAY,
        self::TIME_GROUP_WEEK,
        self::TIME_GROUP_MONTH,
        self::TIME_GROUP_YEAR
    ];

    /**
     * @var string
     */
    protected $chartTitle = "";

    /**
     * constructor
     */
    public function __construct()
    {
        $this->dataTable       = new DataTable();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getAllowedSubMetrics()
    {
        return $this->allowedSubMetrics;
    }

    /**
     * @return array
     */
    public function getAllowedFilters()
    {
        $allowedFilters = collect($this->allowedFilters)->reject(function ($item) {
            return isset($item["show_in_widget"]) && $item["show_in_widget"] == false;
        });
        return $allowedFilters->toArray();
    }

    /**
     * @param        $subMetric
     * @param array  $filters
     *
     * @throws BadMethodCallException
     * @return mixed
     */
    public function getValues($subMetric, $filters = [])
    {
        if (!array_key_exists($subMetric, $this->allowedSubMetrics)) {
            throw new BadMethodCallException('Submetric ' . $subMetric . ' does not exist.');
        } else {
            $methodName = 'calculate' . studly_case($subMetric);
            if (method_exists($this, $methodName)) {
                return call_user_func([$this, $methodName], $filters);
            }
            throw new BadMethodCallException('Method ' . $methodName . ' does not exist.');
        }

    }

    /**
     * Return the chart title
     * @return string
     */
    public function getTitle()
    {
        return $this->chartTitle;
    }

    /**
     * @param string $timeGroup
     */
    public function setTimeGroup($timeGroup)
    {
        $this->timeGroup = $timeGroup;
    }

    /**
     * @param string $chartType
     */
    public function setChartType($chartType)
    {
        $this->chartType = $chartType;
    }

    /**
     * @param       $query
     * @param array $filters
     *
     * @throws BadMethodCallException
     * @return mixed
     */
    protected function applyFilters($query, $filters = [])
    {
        foreach ($filters as $filter) {
            $filterName = $filter[ 'name' ];
            $value      = $filter[ 'value' ];

            $methodName = 'filter' . studly_case($filterName);
            if (method_exists($this, $methodName)) {
                $query = call_user_func([$this, $methodName], $query, $value);
            }
        }
        return $query;
    }


    /**
     * @param $query
     *
     * @return mixed
     */
    protected function applyTimeGroup($query)
    {
        switch ($this->timeGroup) {
            case self::TIME_GROUP_DAY:
                $dbQuery = 'DATE(created_at)';
                break;
            case self::TIME_GROUP_WEEK:
                $dbQuery = 'CONCAT( YEAR(created_at), "-", WEEK(created_at) )';
                break;
            case self::TIME_GROUP_MONTH:
                $dbQuery = 'CONCAT( YEAR(created_at), "-", MONTH(created_at) )';
                break;
            case self::TIME_GROUP_YEAR:
                $dbQuery = 'YEAR(created_at)';
                break;
            default:
                return $query;
                break;
        }
        $query->groupBy(DB::raw($dbQuery));
        $this->queryFields[] = DB::raw($dbQuery . ' as time');
        return $query;
    }

    /**
     * @return string
     */
    protected function getColumnTypeForTimeGroup()
    {
        if ($this->chartType == "PieChart") {
            return "string";
        }

        if ($this->timeGroup == self::TIME_GROUP_DAY) {
            return "date";
        }
        return "string";
    }


    /**
     * When grouping the results e.g. by month, we want to
     * fill the date gaps from the MySQL results
     *
     * @param $results
     * @param $filters
     *
     * @return Collection
     */
    protected function fillDateGaps($results, $filters)
    {

        foreach ($filters as $filter) {
            if ($filter[ 'name' ] == "from_date") {
                $fromDate = $filter[ 'value' ];
            }
            if ($filter[ 'name' ] == "until_date") {
                $untilDate = $filter[ 'value' ];
            }
        }


        $collection = new Collection();

        if ($this->timeGroup == self::TIME_GROUP_DAY) {

            while (strtotime($fromDate) <= strtotime($untilDate)) {
                // prüfen ob $fromDate im Result
                $countForTime = $results->where('time', $fromDate)->first();

                $count = 0;
                if ($countForTime) {
                    $count = $countForTime->count;
                }
                $collection->add((object)['time' => $fromDate, 'count' => $count]);

                $fromDate = date("Y-m-d", strtotime("+1 day", strtotime($fromDate)));
            }

            return $collection;

        }
        if ($this->timeGroup == self::TIME_GROUP_WEEK) {
            while (strtotime($fromDate) <= strtotime($untilDate)) {
                // prüfen ob $fromDate im Result
                $year         = date("Y", strtotime($fromDate));
                $week         = date("W", strtotime($fromDate));
                $timeValue    = $year . "-" . $week;
                $countForTime = $results->where('time', $timeValue)->first();

                $count = 0;
                if ($countForTime) {
                    $count = $countForTime->count;
                }
                $collection->add((object)['time' => $timeValue, 'count' => $count]);

                $fromDate = date("Y-m-d", strtotime("+7 days", strtotime($fromDate)));
            }

            return $collection;

        }
        if ($this->timeGroup == self::TIME_GROUP_MONTH) {

            while (strtotime($fromDate) <= strtotime($untilDate)) {
                // prüfen ob $fromDate im Result
                $year         = date("Y", strtotime($fromDate));
                $week         = date("n", strtotime($fromDate));
                $timeValue    = $year . "-" . $week;
                $countForTime = $results->where('time', $timeValue)->first();

                $count = 0;
                if ($countForTime) {
                    $count = $countForTime->count;
                }
                $collection->add((object)['time' => $timeValue, 'count' => $count]);

                $fromDate = date("Y-m-d", strtotime("+1 month", strtotime($fromDate)));
            }

            return $collection;

        }

        return $results;
    }

    /**
     * @param $query
     * @param $value
     *
     * @return mixed
     */
    abstract protected function filterFromDate($query, $value);

    /**
     * @param $query
     * @param $value
     *
     * @return mixed
     */
    abstract protected function filterUntilDate($query, $value);


}