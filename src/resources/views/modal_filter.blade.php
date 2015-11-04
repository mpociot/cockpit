

<script id="filter-template" type="text/template">

    <div class="form-group" v-if="widget.submetric != '' && widget.hasFilters">
        <label class="col-sm-4 control-label" for="filters">Filters:</label>
        <div class="col-sm-4">
            <select class="form-control"
                    v-if="submetric != ''"
                    v-model="name"

                    >
                <option value="">No filter</option>
                <option v-for="obj in selectableFilters" :value="obj.name">@{{obj.name}}</option>
            </select>
        </div>


        <div class="col-sm-3" v-if="widget.hasFilters && selectableFilters[name]">
            <!-- SELECT FILTER -->
            <select v-if="selectableFilters[name].type == 'select'"
                    class="form-control"
                    v-model="value"
                    >
                <option disabled value="">Filter</option>
                <option v-for="obj in selectableFilters[name].options" :value="$key">@{{ obj }}</option>
            </select>

            <!-- CHECKBOX FILTER -->
            <input  v-if="selectableFilters[name].type == 'checkbox'"
                    type="checkbox" class="form-control"
                    v-model="value"
                    value="1" />

            <!-- TEXT FILTER -->
            <input v-if="selectableFilters[name].type == 'text'"
                   type="text" class="form-control"
                   v-model="value" />

        </div>

        <div class="col-sm-1">
            <a href="#"  @click="deleteFilter" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
        </div>
    </div>

</script>