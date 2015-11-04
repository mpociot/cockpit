<?php namespace Mpociot\Cockpit;

use Carbon\Carbon;

class Filter
{
    /**
     * @var Carbon
     */
    private $fromDate;

    /**
     * @var Carbon
     */
    private $untilDate;

    /**
     * All pre-defined time filters
     * @var array
     */
    protected $allowedTimeFilters = [
        'all',
        'today',
        'yesterday',
        'last_7',
        'this_week',
        'last_week',
        'this_month',
        'last_month',
        'this_year',
        'last_year'
    ];

    /**
     * Initially display the last 7 days
     */
    public function __construct()
    {
        $this->setTimeRange("last_7");
    }

    /**
     * @param string $fromDate
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;
    }

    /**
     * @param string $untilDate
     */
    public function setUntilDate($untilDate)
    {
        $this->untilDate = $untilDate;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $filters   = [];
        $filters[] = ['name' => 'from_date', 'value' => $this->fromDate->toDateTimeString()];
        $filters[] = ['name' => 'until_date', 'value' => $this->untilDate->toDateTimeString()];
        return $filters;
    }

    /**
     * Set a pre-defined filter to use
     * @param string $time_range
     */
    public function setTimeRange($time_range)
    {
        switch ($time_range) {
            case "all":
                $this->setUntilDate(Carbon::now());
                $this->setFromDate(Carbon::minValue());
                break;
            case "today":
                $this->setUntilDate(Carbon::now());
                $this->setFromDate(Carbon::today());
                break;
            case "yesterday":
                $this->setUntilDate(Carbon::today());
                $this->setFromDate(Carbon::today()->subDay(1));
                break;
            case "last_7":
                $this->setUntilDate(Carbon::now());
                $this->setFromDate(Carbon::today()->subDays(7));
                break;
            case "this_week":
                $this->setUntilDate(Carbon::now()->endOfWeek());
                $this->setFromDate(Carbon::now()->startOfWeek());
                break;
            case "last_week":
                $this->setUntilDate(Carbon::now()->endOfWeek()->subDays(7));
                $this->setFromDate(Carbon::now()->startOfWeek()->subDays(7));
                break;
            case "this_month":
                $this->setUntilDate(Carbon::now()->endOfMonth());
                $this->setFromDate(Carbon::now()->startOfMonth());
                break;
            case "last_month":
                $this->setUntilDate(Carbon::now()->endOfMonth()->subMonths(1));
                $this->setFromDate(Carbon::now()->startOfMonth()->subMonth(1));
                break;
            case "this_year":
                $this->setUntilDate(Carbon::now()->endOfYear());
                $this->setFromDate(Carbon::now()->startOfYear());
                break;
            case "last_year":
                $this->setUntilDate(Carbon::now()->endOfYear()->subYears(1));
                $this->setFromDate(Carbon::now()->startOfYear()->subYears(1));
                break;
        }
    }


}