// Bind the timepicker
$(function(){
    $('#picker_from_date').datepicker({
        pickTime: false,
        dateFormat: 'yy-mm-dd',
        startDate: '-30d',
        showClose: 'true'
    });

    $('#picker_until_date').datepicker({
        pickTime: false,
        dateFormat: 'yy-mm-dd'
    });
})