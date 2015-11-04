<html>
<head>
    <link rel="stylesheet" href="/css/cockpit/cockpit.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/0.2.3/gridstack.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/0.2.3/gridstack-extra.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker3.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>

    <!-- VueJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.4/vue.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.1.16/vue-resource.min.js"></script>

    <!-- Lavacharts JS API-->
    {!! \Lava::jsapi() !!}

    <script>
        window.widgets = {!! $widgets !!};
    </script>

    <!-- Grid releveant scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/0.2.3/gridstack.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.min.js"></script>

</head>
<body>
<br/>
<br/>
    <div class="container">
        <div id="dashboard-app" v-cloak>

            <input type="hidden" id="token" value="{{ csrf_token() }}" />

            <a @click="newWidget" class="btn btn-labeled btn-primary">
                 <span class="btn-label">
                  <i class="glyphicon glyphicon-plus"></i>
                 </span> New widget
            </a>

            @include('cockpit::filters')
            <br /><br />

            @include('cockpit::widget')

            <div class="widget_container">
                <div class="grid-stack grid-stack-4" v-el:grid>
                    <dashboard-widget v-for="widget in widgets" v-ref:widgets :data="widget"></dashboard-widget>
                </div>
            </div>

            @include('cockpit::modal')
        </div>
    </div>

    <!-- VueJS app -->
    <script src="/js/cockpit/global_filter.js"></script>
    <script src="/js/cockpit/widget.js"></script>
    <script src="/js/cockpit/filter.js"></script>
    <script src="/js/cockpit/app.js"></script>
    <script type="text/javascript" src="//www.google.com/jsapi"></script>
</body>
</html>