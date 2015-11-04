<?php namespace Mpociot\Cockpit\Controller;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Mpociot\Cockpit\Filter;
use Mpociot\Cockpit\MetricHandler;
use Mpociot\Cockpit\Repositories\WidgetRepository;
use Mpociot\Cockpit\Widget;

class CockpitController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view("cockpit::index")
            ->with("widgets", json_encode(Widget::all()));
    }

    /**
     * output: metrics-json
     *
     * @param WidgetRepository $wr
     *
     * @return array
     */
    public function getMetrics(WidgetRepository $wr)
    {
        return $wr->getAvailableMetrics();
    }

    /**
     * Show a specific widget
     *
     * @return Response
     */
    public function show($widget_id, Filter $globalFilter)
    {
        if (Session::has("cockpit.from_date")) {
            $globalFilter->setFromDate(new Carbon(Session::get("cockpit.from_date")));
        }
        if (Session::has("cockpit.until_date")) {
            $globalFilter->setUntilDate(new Carbon(Session::get("cockpit.until_date")));
        }
        if (Session::has("cockpit.time_range")) {
            $globalFilter->setTimeRange(Session::get("cockpit.time_range"));
        }
        $widget        = Widget::findOrFail($widget_id);
        $handler       = new MetricHandler($globalFilter);
        $widgetFilters = is_array($widget->filters) ? $widget->filters : [];
        return $handler->renderChart($widget, $widgetFilters, $widget->timegroup);
    }

    /**
     * Create a new widget.
     *
     * @return Response
     */
    public function create(WidgetRepository $wr)
    {
        $widget = $wr->saveWidget(new Widget());
        return $widget->toArray();
    }


    /**
     * Update an existing widget.
     *
     * @param                  $widget_id
     * @param WidgetRepository $wr
     * @param Filter $filter
     *
     * @return Response
     */
    public function save($widget_id, WidgetRepository $wr, Filter $filter)
    {
        $widget = Widget::findOrFail($widget_id);

        $wr->saveWidget($widget);

        return $widget->toArray();
    }

    /**
     * Save temporary global settings in the session
     *
     * @param Request $request
     * @return array
     */
    public function saveSettings(Request $request)
    {
        Session::set("cockpit.from_date", $request->from_date);
        Session::set("cockpit.until_date", $request->until_date);
        Session::set("cockpit.time_range", $request->time_range);
        return ["success" => true];
    }

    /**
     * @param $widget_id
     *
     * @return Response
     */
    public function delete($widget_id)
    {
        $widget = Widget::findOrFail($widget_id);
        $widget->delete();

        return ["success" => true];
    }

    /**
     * @param $metric
     *
     * @return array
     */
    public function getFiltersForMetric($metric)
    {
        $class   = Config::get("cockpit.metrics_namespace",
                "App\\Cockpit\\Metrics\\") . $metric;
        $filters = [];
        if (class_exists($class)) {
            $metric  = app($class);
            $filters = $metric->getAllowedFilters();

            foreach ($filters as $filterName => &$filterOptions) {
                if ($filterOptions[ "type" ] == "select") {
                    $getter                     = "get" . studly_case($filterName) . "Options";
                    $filterOptions[ "options" ] = call_user_func([$metric, $getter]);
                }
            }

        }
        return $filters;
    }


    /**
     * @param Request $request
     */
    public function savePosition(Request $request)
    {
        $grid = $request->get("grid", "[]");
        $grid = json_decode($grid);
        foreach ($grid as $widgetPosition) {
            $widget         = Widget::find($widgetPosition->id);
            $widget->col    = $widgetPosition->col;
            $widget->row    = $widgetPosition->row;
            $widget->size_x = $widgetPosition->size_x;
            $widget->size_y = $widgetPosition->size_y;
            $widget->save();
        }
    }

}