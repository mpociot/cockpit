<div class="modal fade" id="widgetModal" v-el:modal data-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" v-if="widget">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <input type="text" class="form-control modal-title" v-model="widget.name" placeholder="Widget name">
            </div>
            <div class="modal-body form-horizontal">
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="metric">Metric:</label>
                    <div class="col-sm-8">
                        <select class="form-control"
                                name="metric"
                                @change="loadFilters"
                                v-model="widget.metric"
                                >
                            <option disabled value="">Metric</option>
                            <option v-for="obj in widget.metrics" :value="obj.id">@{{obj.name}}</option>
                        </select>
                    </div>
                </div>
                <div v-if="widget.metric != ''" class="form-group">
                    <label class="col-sm-4 control-label" for="submetric">Submetric:</label>
                    <div class="col-sm-8">
                        <select name="submetric" class="form-control"
                                v-model="widget.submetric"
                                >
                            <option disabled value="">Submetric</option>
                            <option v-for="obj in widget.submetrics[widget.metric]" :value="obj.id">@{{obj.name}}</option>
                        </select>
                    </div>
                </div>

                <div v-if="widget.submetric != ''" class="form-group">
                    <label class="col-sm-4 control-label" for="charttype">Chart type:</label>
                    <div class="col-sm-8">
                        <select name="charttype" class="form-control"
                                v-model="widget.charttype"
                                >
                            <option disabled value="">Chart type</option>
                            <option v-for="obj in widget.availableMetrics[widget.metric].submetrics[widget.submetric]" :value="obj">@{{obj}}</option>
                        </select>
                    </div>
                </div>

                <div v-if="widget.hasTimeGroup" class="form-group">
                    <label class="col-sm-4 control-label" for="timegroup">Time group:</label>
                    <div class="col-sm-8">
                        <select name="timegroup" class="form-control"
                                v-model="widget.timegroup"
                                >
                            <option disabled value="">Time group</option>
                            <option v-for="obj in widget.timegroups" :value="obj.id">@{{obj.name}}</option>
                        </select>
                    </div>
                </div>

                <input type="hidden"
                       v-if="! widget.hasTimeGroup"
                       v-model="widget.timegroup"
                       value=""
                        >

                <div class="form-group col-sm-12 pull-right"  v-if="widget.viewtype != '' && widget.hasFilters">
                    <button @click="addFilter" class="btn btn-sm pull-right"><i class="fa fa-plus"> </i>
                    Add filter
                    </button>
                </div>

                <widget-filter v-for="filter in widget.filters" v-ref:selectedfilters :data="filter" :widget="widget"></widget-filter>


                <div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <button v-if="widget.charttype != ''"class="btn btn-success" @click="saveWidget" >
                <i class="fa fa-refresh fa-spin" v-if="saving"></i>
                Save
                </button>
            </div>
        </div>
    </div>
</div>


@include('cockpit::modal_filter')