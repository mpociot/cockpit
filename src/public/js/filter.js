var WidgetFilter = Vue.extend({

    template: '#filter-template',

    /*
     * Bootstrap the component. Load the initial data.
     */
    ready: function () {
        if (this.data) {
            for (var key in this.data) {
                this.$set(key, this.data[key]);
            }
        }
    },

    props: ['data', 'widget'],

    /*
     * Initial state of the component's data.
     */
    data: function () {
        return {
            name: "",
            value: "",
            type: ""
        }
    },

    computed: {

        postData: function () {
            return {
                name: this.name,
                value: this.value
            };
        },

        selectableFilters: function () {

            var availableFilters = this.widget.availableFilters;
            var returnFilters = {};
            var selectedFilters = this.$parent.$refs.selectedfilters;

            for (var index in availableFilters) {
                var found = false;
                for (var indexS in selectedFilters) {
                    if (availableFilters[index].name == selectedFilters[indexS].name && selectedFilters[indexS].name !== this.name) {
                        found = true;
                    }
                }

                if (!found) {
                    returnFilters[availableFilters[index].name] = availableFilters[index];
                }
            }
            return returnFilters;
        },

    },

    methods: {
        deleteFilter: function () {
            this.widget.filters.$remove(this.data);
        }
    }

});


Vue.component('widget-filter', WidgetFilter);