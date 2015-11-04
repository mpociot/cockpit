<script id="widget-template" type="text/template">

    <div    class="grid-stack-item"
            data-widget="@{{ id }}"
            data-gs-y="@{{ row }}"
            data-gs-x="@{{ col }}"
            data-gs-width="@{{ size_x }}"
            data-gs-height="@{{ size_y }}"
            >
        <div class="grid-stack-item-content">

            <div class="widget panel panel-default">
                <div class="panel-heading widget clearfix">
                    <h4 class="pull-left">@{{ name }}</h4>
                    <div class="btn-group pull-right">
                        <a href="#"  @click="editWidget" class="btn btn-primary btn-sm edit"><i class="fa fa-pencil"></i></a>
                        <a href="#"  @click="duplicateWidget" class="btn btn-primary btn-sm duplicate"><i class="fa fa-clone"></i></a>
                        <a href="#"  @click="deleteWidget" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i></a>
                    </div>
                </div>

                <div class="panel-body widgetcontent" data-widgetid="@{{ id }}" id="widget_@{{ id }}">
                    <i v-if="loading" class="loading_img fa fa-3x fa-refresh fa-spin"></i>
                    <div v-if="! loading" v-execute-js="chart"></div>
                </div>
            </div>
        </div>
    </div>

</script>