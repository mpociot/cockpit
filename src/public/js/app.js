Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

var DashboardApp = new Vue({

    el: '#dashboard-app',

    data: {
        availableMetrics: [],

        // Load all widgets from window.widgets
        widgets: window.widgets,

        saving: false,

        restoreState: false,

        loadingSettings: false,

        widget: false,

        originalWidget: {},
        originalFilters: {},

        timegroups: [
            {id: "DAY", name: "Day"},
            {id: "WEEK", name: "Week"},
            {id: "MONTH", name: "Month"},
            {id: "YEAR", name: "Year"}
        ],

        // Global filters
        settingsForm: {
            from_date: "",
            until_date: "",
        }

    },

    /**
     * Upon creation, load the gridstack plugin and all available
     * metrics from the API.
     */
    created: function () {
        var self = this;
        // Load all available metrics
        this.$http
            .get('/cockpit/api/metrics')
            .success(function (data) {
                this.availableMetrics = data;
            });

        // Load the gridstack plugin
        $(".grid-stack").gridstack({
            width: 4,
            cell_height: 100,
            vertical_margin: 10
        }).on('dragstop resizestop', function () {
            self.saveGrid();
        });
    },

    /**
     * Bind an event when the modal view gets closed.
     * If we're creating a new widget - simply remove it
     * If we're editing an existing widget - restore it's state
     */
    ready: function () {
        var self = this;

        // Set modal event listeners
        $(this.$els.modal).on('hidden.bs.modal', function () {
            // Destroy widget if new modal get's closed
            if (self.widget.id === false) {
                self.widget.destroyWidget();
            } else if( self.restoreState ){
                // Restore widget state
                for (var key in self.originalWidget) {
                    self.widget.$set(key, self.originalWidget[key]);
                }
                self.widget.$set("filters", this.originalFilters);
            }
        });
    },

    methods: {



        /**
         * Update temporary global settings
         */
        updateGlobalSettings: function () {
            this.loadingSettings = true;
            this.$http.put('/cockpit/api/settings', this.settingsForm).success(function (data) {
                this.loadingSettings = false;
                this.$children.forEach(function (widget) {
                    widget.loadChart();
                });
            });
        },

        /**
         * Load all available filters for the widget
         * currently being edited
         *
         * @param callback
         */
        loadFilters: function (callback) {
            this.$http.get('/cockpit/api/filters/' + this.widget.metric)
                .success(function (data) {
                    this.widget.$set("availableFilters", data);
                    if (callback && typeof(callback) === 'function') {
                        callback();
                    }
                });
        },

        /**
         * Save position / size changes
         */
        saveGrid: function () {
            var self = this;
            setTimeout(function () {
                var res = _.map($('.grid-stack .grid-stack-item:visible'), function (el) {
                    el = $(el);
                    var node = el.data('_gridstack_node');
                    return {
                        id: el.attr('data-widget'),
                        col: node.x,
                        row: node.y,
                        size_x: node.width,
                        size_y: node.height
                    };
                });
                self.$http.post('/cockpit/api/savePosition', {grid: JSON.stringify(res)});
            }, 200);
            try {
                window.lava.redrawCharts()
            } catch (Exception) {
                // Not all charts loaded yet...
            }
        },

        /**
         * Duplicate an existing widget
         * @param widgetData
         */
        duplicateWidget: function (widgetData) {
            var self = this;

            this.$http.post('/cockpit/api', widgetData)
                .success(function (data) {

                    self.widgets.push({
                        id: data.id,
                        name: data.name,
                        metric: widgetData.metric,
                        submetric: widgetData.submetric,
                        charttype: widgetData.charttype,
                        filters: widgetData.filters,
                        timegroup: widgetData.timegroup,
                        size_x: widgetData.size_x,
                        size_y: widgetData.size_y
                    });

                    Vue.nextTick(function () {
                        self.saveGrid();
                    });
                });
        },

        /**
         * Create a new widget
         */
        newWidget: function () {
            var self = this;
            this.widgets.push({
                name: "New widget"
            });
            // Open the modal
            Vue.nextTick(function () {
                self.widget = self.$children[self.$children.length - 1];
                self.widget.availableMetrics = self.availableMetrics;
                Vue.nextTick(function () {
                    $(self.$els.modal).modal();
                });
            });
        },

        /**
         * Show the edit widget modal
         * @param widget
         */
        editWidget: function (widget) {
            var self = this;
            widget.availableMetrics = this.availableMetrics;
            // Duplicate filters and widget to restore the state
            this.originalFilters = Vue.util.extend({}, widget.filters || {});
            this.originalWidget = Vue.util.extend({}, widget);

            this.widget = widget;
            this.restoreState = true;

            if (widget.metric != "") {
                this.loadFilters(function () {
                    $(self.$els.modal).modal();
                });
            } else {
                $(self.$els.modal).modal();
            }
        },

        /**
         * Prepare the filter components for saving through the REST API
         * @param filters
         * @returns {Array}
         */
        prepareFilters: function (filters) {
            var filterArray = [];
            for (var index in filters) {
                if (filters[index].name != "") {
                    filterArray.push(filters[index].postData);
                }
            }
            return filterArray;
        },

        /**
         * Save widget settings
         */
        saveWidget: function () {
            var self = this;
            this.saving = true;
            var widget = this.widget;

            var filterArray = this.prepareFilters( this.$refs.selectedfilters );

            var widgetData = {
                name: widget.name,
                metric: widget.metric,
                submetric: widget.submetric,
                charttype: widget.charttype,
                timegroup: widget.timegroup,
                filter: filterArray
            };

            var savedSuccessfully = function (data) {
                self.saving = false;
                widget.id = data.id;
                widget.$set("filters", filterArray);

                self.restoreState = false;
                // Close the modal
                $(this.$els.modal).modal('hide');

                // Load the chart
                widget.loadChart();

                // update position
                this.saveGrid();
            };

            if (widget.id) {
                this.$http.put('/cockpit/api/' + widget.id, widgetData).success(savedSuccessfully);
            } else {
                this.$http.post('/cockpit/api', widgetData).success(savedSuccessfully);
            }
        },

        /**
         * Add a new filter
         */
        addFilter: function () {
            var filter = {};
            if (this.widget.filters === null) {
                this.widget.filters = [];
            }
            this.widget.filters.push(filter);
        }
    }
});

