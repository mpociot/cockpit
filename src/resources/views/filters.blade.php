
<div >
    <div class="well" id="timepicker">
        <form>
            <div id="picker_from" class="input-append">
                <label for="picker_from_date">From:</label><br>
                <input v-model="settingsForm.from_date" class="form-control" name="picker_from_date" id="picker_from_date" data-format="yyyy-MM-dd" type="text" value="{{session('cockpit.from_date')}}" />
            </div>
            <div id="picker_till" class="input-append">
                <label for="picker_until_date">Until:</label><br>
                <input v-model="settingsForm.until_date" class="form-control" name="picker_until_date" id="picker_until_date" data-format="yyyy-MM-dd" type="text" value="{{session('cockpit.until_date')}}" />
            </div>

            <a @click="updateGlobalSettings" id="update_timerange" class="btn btn-labeled btn-primary btn-sm">
                <template v-if="! loadingSettings">
                    <span class="btn-label"><i class="glyphicon glyphicon-check"></i></span>
                    <span>Apply</span>
                </template>
                <template v-else>
                    <span class="btn-label"><i class="fa fa-refresh fa-spin"></i></span>
                    <span>Loading</span>
                </template>
            </a>
        </form>
    </div>
</div>