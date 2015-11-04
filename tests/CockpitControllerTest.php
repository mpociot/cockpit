<?php

use Illuminate\Support\Facades\Session;
use Mockery as m;
use Mpociot\Cockpit\Widget;

class CockpitTest extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Mpociot\Cockpit\CockpitServiceProvider'];
    }

    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../src/database'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app[ 'config' ]->set('database.default', 'testing');
        $app[ 'config' ]->set('cockpit.metrics_namespace', "Mpociot\\Tests\\");
        $app[ 'config' ]->set('cockpit.metrics_path', __DIR__ . "/Metrics/");

        \Schema::create('test_models', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function testCanGetAllMetrics()
    {
        $this->visit("/cockpit/api/metrics")->seeJson([
            "Users" => [
                "key"        => "",
                "submetrics" => [
                    'one_chart'  => ['PieChart'],
                    'two_charts' => ['LineChart', 'PieChart'],
                    'per_time' => ['LineChart'],
                ],
                "filters"    => [
                    'username' => [
                        'type' => 'text',
                        'name' => 'Username'
                    ]
                ]
            ]
        ]);
    }

    public function testCanShowWidget()
    {
        $widget            = new Widget();
        $widget->name      = "New widget";
        $widget->metric    = "Users";
        $widget->submetric = "two_charts";
        $widget->charttype = "LineChart";
        $widget->filters   = [];
        $widget->timegroup = "";
        $widget->col       = 0;
        $widget->row       = 0;
        $widget->size_x    = 1;
        $widget->size_y    = 2;
        $widget->save();

        $this->visit("/cockpit/api/" . $widget->getKey());
        $this->seeStatusCode(200);
        $this->assertContains('<div id="chart_' . $widget->getKey() . '">', $this->response->getContent());
    }

    public function testCanShowWidgetWithGlobalFilter()
    {
        $widget            = new Widget();
        $widget->name      = "New widget";
        $widget->metric    = "Users";
        $widget->submetric = "per_time";
        $widget->charttype = "LineChart";
        $widget->filters   = [];
        $widget->timegroup = "DAY";
        $widget->col       = 0;
        $widget->row       = 0;
        $widget->size_x    = 1;
        $widget->size_y    = 2;
        $widget->save();

        Session::set("cockpit.from_date", "2015-10-01");
        Session::set("cockpit.until_date", "2015-11-01");

        $this->visit("/cockpit/api/" . $widget->getKey());
        $this->seeStatusCode(200);
        $this->assertContains('<div id="chart_' . $widget->getKey() . '">', $this->response->getContent());
    }

    public function testCanShowWidgetWithTimegroupDay()
    {
        $widget            = new Widget();
        $widget->name      = "New widget";
        $widget->metric    = "Users";
        $widget->submetric = "per_time";
        $widget->charttype = "LineChart";
        $widget->filters   = [];
        $widget->timegroup = "DAY";
        $widget->col       = 0;
        $widget->row       = 0;
        $widget->size_x    = 1;
        $widget->size_y    = 2;
        $widget->save();

        $this->visit("/cockpit/api/" . $widget->getKey());
        $this->seeStatusCode(200);
        $this->assertContains('<div id="chart_' . $widget->getKey() . '">', $this->response->getContent());
    }


    public function testCanCreateWidget()
    {
        $widgetData = [
            "name"      => "new widget",
            "metric"    => "Users",
            "submetric" => "two_charts",
            "charttype" => "LineChart",
            "filters"   => json_encode([]),
            "timegroup" => "",
            "col"       => 0,
            "row"       => 0,
            "size_x"    => 1,
            "size_y"    => 2,
        ];
        $this->post("/cockpit/api/", $widgetData);
        $widgetData[ "filters" ] = [];
        $this->seeJsonContains($widgetData);
        $this->seeStatusCode(200);
    }

    public function testCanUpdateWidget()
    {

        $widget            = new Widget();
        $widget->name      = "New widget";
        $widget->metric    = "Users";
        $widget->submetric = "two_charts";
        $widget->charttype = "LineChart";
        $widget->filters   = [];
        $widget->timegroup = "";
        $widget->col       = 0;
        $widget->row       = 0;
        $widget->size_x    = 1;
        $widget->size_y    = 2;
        $widget->save();

        $widgetData = [
            "name"      => "modified name",
            "metric"    => "Users",
            "submetric" => "one_charts",
            "charttype" => "PieChart",
            "timegroup" => ""
        ];
        $this->put("/cockpit/api/" . $widget->getKey(), $widgetData);

        $this->seeJsonContains($widgetData);
        $this->seeStatusCode(200);
    }

    public function testCanSetGlobalSettings()
    {
        $this->put("/cockpit/api/settings", [
            "from_date"  => strftime("%Y-%m-%d"),
            "until_date" => strftime("%Y-%m-%d", strtotime("-1 month")),
            "time_range" => "all",
        ])->seeJson();
        $this->seeStatusCode(200);

        $this->assertTrue(Session::has("cockpit.from_date"));
        $this->assertTrue(Session::has("cockpit.until_date"));
        $this->assertTrue(Session::has("cockpit.time_range"));
        $this->assertEquals("all",Session::get("cockpit.time_range"));
    }

    public function testCanDeleteWidget()
    {
        $widget            = new Widget();
        $widget->name      = "New widget";
        $widget->metric    = "Users";
        $widget->submetric = "two_charts";
        $widget->charttype = "LineChart";
        $widget->filters   = [];
        $widget->timegroup = "";
        $widget->col       = 0;
        $widget->row       = 0;
        $widget->size_x    = 1;
        $widget->size_y    = 2;
        $widget->save();


        $this->delete("/cockpit/api/" . $widget->getKey())->seeJson();
        $this->seeStatusCode(200);

        $this->assertNull(Widget::find($widget->getKey()));
    }

    public function testGetFiltersForMetric()
    {
        $this->visit("/cockpit/api/filters/Users")
            ->seeJson([
                'username' => [
                    'type' => 'text',
                    'name' => 'Username'
                ]
            ])
            ->seeStatusCode(200);

        $this->visit("/cockpit/api/filters/MetricWithSelect")
            ->seeJson([
                'options' => [
                    'type' => 'select',
                    'name' => 'Username',
                    'options' => [ "1" => "Yes","0" => "No"]
                ]
            ])
            ->seeStatusCode(200);
    }

    public function testCanSaveWidgetPositions()
    {
        Widget::unguard();
        $widget1            = Widget::create([
            "name" => "New widget",
            "metric" => "Users",
            "submetric" => "two_charts",
            "charttype" => "LineChart",
            "filters" => [],
            "timegroup" => "",
            "col" => 0,
            "row" => 0,
            "size_x" => 1,
            "size_y" => 2
        ]);


        $widget2            = Widget::create([
            "name" => "New widget",
            "metric" => "Users",
            "submetric" => "two_charts",
            "charttype" => "LineChart",
            "filters" => [],
            "timegroup" => "",
            "col" => 0,
            "row" => 0,
            "size_x" => 1,
            "size_y" => 2
        ]);

        $gridData = [
            (object)array(
                "id" => $widget1->getKey(),
                "col" => 1,
                "row" => 2,
                "size_x" => 3,
                "size_y" => 4,
            ),
            (object)array(
                "id" => $widget2->getKey(),
                "col" => 5,
                "row" => 6,
                "size_x" => 7,
                "size_y" => 8,
            )
        ];

        $this->post("/cockpit/api/savePosition", [
            "grid" => json_encode( $gridData )
        ] );
        $this->seeJson();
        $this->seeStatusCode(200);

        $this->seeInDatabase( "widgets" , [
            "id" => $widget1->getKey(),
            "col" => 1,
            "row" => 2,
            "size_x" => 3,
            "size_y" => 4,
        ]);

        $this->seeInDatabase( "widgets" , [
            "id" => $widget2->getKey(),
            "col" => 5,
            "row" => 6,
            "size_x" => 7,
            "size_y" => 8,
        ]);
    }



    public function testCanShowWidgetWithTimeranges()
    {
        $widget            = new Widget();
        $widget->name      = "New widget";
        $widget->metric    = "Users";
        $widget->submetric = "per_time";
        $widget->charttype = "LineChart";
        $widget->filters   = [];
        $widget->timegroup = "DAY";
        $widget->col       = 0;
        $widget->row       = 0;
        $widget->size_x    = 1;
        $widget->size_y    = 2;
        $widget->save();

        $timeranges = [
            "all","today","yesterday","last_7","this_week","last_week","this_month","last_month","this_year","last_year"
        ];

        foreach( $timeranges AS $timerange )
        {
            Session::set("cockpit.time_range", $timerange);

            $this->visit("/cockpit/api/" . $widget->getKey());
            $this->seeStatusCode(200);
            $this->assertContains('<div id="chart_' . $widget->getKey() . '">', $this->response->getContent());
        }
    }
}



class TestModel extends \Illuminate\Database\Eloquent\Model
{

}