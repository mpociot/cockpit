var DashboardWidget = Vue.extend({

    template: '#widget-template',

    // Data property used to load the widgets
    props: ['data'],

    /*
     * Initial state of the component's data.
     */
    data: function () {
        return {
            // Basic data
            id: false,

            name: "",
            metric: "",
            submetric: "",
            charttype: "",

            filters: [],
            availableFilters: [],

            loading: true,

            timegroup: "Day",
            timegroups: this.$parent.timegroups,

            chart: "",
            availableMetrics: [],

            row: false,
            col: false,

            // Default widget size
            size_x: 1,
            size_y: 2
        }


    },

    /*
     * Bootstrap the component. Load the initial data.
     */
    ready: function () {
        // If the data property exists, we iterate over it and set this as our $data
        if (this.data) {
            for (var key in this.data) {
                this.$set(key, this.data[key]);
            }
        }

        // Load chart if available
        if (this.id) {
            this.loadChart();
        }

        /**
         * Position the widget inside the grid
         *
         * If no row and column is given, just append the widget to the
         * grid. The grid-stack plugin will take care of finding
         * a place for the widget
         */
        if (this.row === false && this.col === false) {
            $('.grid-stack').data('gridstack').add_widget(this.$el, 0, 0, this.size_x, this.size_y, true);
        } else {
            $('.grid-stack').data('gridstack').add_widget(this.$el, this.col, this.row, this.size_x, this.size_y);
        }
    },

    computed: {

        /**
         * Get all filter names
         *
         * @returns {Array}
         */
        filterNames: function () {
            var names = [];
            for (var filter in this.filters) {
                names.push(filter);
            }
            return names;
        },

        /**
         * Get all metrics
         *
         * @returns {Array}
         */
        metrics: function () {
            var metrics = [];
            for (var metric in this.availableMetrics) {
                metrics.push({id: metric, name: metric});
            }
            return metrics;
        },

        /**
         * Get all submetrics for the current metric
         *
         * @returns {{}}
         */
        submetrics: function () {
            var result = {};
            for (var metric in this.availableMetrics) {
                var submetrics = [];
                for (var submetric in this.availableMetrics[metric].submetrics) {
                    submetrics.push({id: submetric, name: submetric});
                }

                result[metric] = submetrics;
            }
            return result;
        },

        /**
         * Is the current submetric a "per_time" submetric?
         *
         * @returns {boolean}
         */
        hasTimeGroup: function () {
            return (this.submetric.indexOf("per_time") !== -1);
        },

        /**
         * Does this widget have filters?
         * @returns {boolean}
         */
        hasFilters: function () {
            return (Object.keys(this.availableFilters).length > 0)
        },
    },

    methods: {

        /**
         * Load and draw the google chart
         */
        loadChart: function () {
            this.loading = true;
            this.$http.get('/cockpit/api/' + this.id)
                .success(function (data) {
                    this.loading = false;
                    this.chart = data;
                });
        },

        /**
         * Edit the widget
         */
        editWidget: function () {
            this.$parent.editWidget(this);
        },

        /**
         * Delete the widget
         */
        deleteWidget: function () {
            if (this.id) {
                this.$http.delete('/cockpit/api/' + this.id)
                    .success(function (data) {
                        this.destroyWidget();
                    });
            } else {
                this.destroyWidget();
            }
        },

        /**
         * Remove the widget from the grid.
         */
        destroyWidget: function () {
            // Remove element from grid
            var grid = $('.grid-stack').data('gridstack');
            grid.remove_widget(this.$el, false);

            // Remove VueJS VM
            this.$destroy(true);
        },

        /**
         * Duplicate a widget
         */
        duplicateWidget: function () {
            this.$parent.duplicateWidget(this.$data);
        }

    }

});


Vue.component('dashboard-widget', DashboardWidget);

Vue.directive('execute-js', {
    bind: function () {
    },
    update: function (value) {
        this.el.innerHTML = value;

        var content = document.createElement('div');
        content.innerHTML = value;

        Vue.nextTick(function () {
            var scripts = content.getElementsByTagName('script');
            for (var ix = 0; ix < scripts.length; ix++) {
                eval(scripts[ix].text);
            }

            setTimeout(function () {
                try {
                    window.lava.redrawCharts()
                } catch (e) {
                    // Not all charts loaded yet...
                }
            }, 500);

        });
    }
});
