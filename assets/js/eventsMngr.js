var initializeEditable = function(event){
    $('#EventName').editable({
        type: 'text',
        pk: event.ID,
        value: event.EventName,
        title: 'שינוי שם'
    });
    $('#EventDate').editable({
        type: 'date',
        format: 'yyyy-mm-dd',
        viewformat: 'dd/mm/yyyy',
        mode: 'popup',
        datepicker: {
            weekstart: 1
        },
        placement: 'bottom',
        pk: event.ID,
        value: event.EventDate,
        title: 'שינוי שם'
    });
    document.getElementById('HebrewDate').innerHTML = event.HebrewDate;
    //     .editable({
    //     type: 'text',
    //     pk: event.ID,
    //     url: '/post/eventHandler.php',
    //     value: event.EventName,
    //     title: 'שינוי שם'
    // });
    $('#EventTime').editable({
        type: 'time',
        placeholder: '19:00',
        pk: event.ID,
        value: event.EventTime,
        title: 'שינוי שם'
    });
};


var getEventData = function () {

    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.url = 'post/eventHandler.php';
    $.fn.editable.defaults.params = {action: 'update'};
    $.fn.editable.defaults.success = getEventData;

    $.ajax({
        type        : "POST",
        url         : "post/eventHandler.php",
        data        : {action: 'getEventData'},
        // contentType: "application/json; charset=utf-8",
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true,
        success     : loadEventData,
        error       : function(jqXHR, status){
            console.log(status);
            console.log(jqXHR);
            bootbox.alert(jqXHR.responseText);
        }
    })
};

var loadEventData = function (data) {
    if (data.status === 'success'){
        $("#event-data").show();
        console.log("received event data");
        console.log(data);
        initializeEditable(data.event);
    } else {
        console.log(data);
    }

};
$(document).on("signedIn", getEventData);
$.when($.ready).then(function(){
    if (isSignedIn) getEventData();
})
var createNewEvent = function (eventData) {

}

$("#addEventForm").submit(function(event){
    // cancels the form submission
    event.preventDefault();
    console.log("in submit");
    submitForm();
});

function submitForm(){
    $('.form-group').removeClass('has-error'); // remove the error class
    $('.help-block').remove(); // remove the error text
    $('.alert-success').remove(); //remove the success text
    // Initiate Variables With Form Content
    var formData = {
        'EventName' : $("#InputName").val(),
        'EventDate' : $("#InputDate").val(),
        'EventTime' : $("#InputTime").val(),
        'Venue'     : $("#InputVenue").val(),
        'Address'   : $("#InputAddress").val(),
    }

    $.ajax({
        type        : "POST",
        url         : "post/eventHandler.php",
        data        : {action: 'create', data: formData},
        // contentType: "application/json; charset=utf-8",
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true,
    })

        .done(function(data) {
            // here we will handle errors and validation messages
            console.log(data);
            if ( data.status !== "success") {
                if (data.errors.newevent){
                    $('#errors').append('<div class="help-block">' + data.errors.newevent + '</div>');
                }
                if (data.errors.user){
                    $('#errors').append('<div class="help-block">' + data.errors.user + '</div>');
                }
                // handle errors for name ---------------
                if (data.errors.name) {
                    $('#name-group').addClass('has-error'); // add the error class to show red input
                    $('#name-group').append('<div class="help-block">' + data.errors.name + '</div>'); // add the actual error message under our input
                }
                if (data.errors.date) {
                    $('#date-group').addClass('has-error'); // add the error class to show red input
                    $('#date-group').append('<div class="help-block">' + data.errors.date + '</div>'); // add the actual error message under our input
                }
                if (data.errors.time) {
                    $('#time-group').addClass('has-error'); // add the error class to show red input
                    $('#time-group').append('<div class="help-block">' + data.errors.time + '</div>'); // add the actual error message under our input
                }
                if (data.errors.venue) {
                    $('#venue-group').addClass('has-error'); // add the error class to show red input
                    $('#venue-group').append('<div class="help-block">' + data.errors.venue + '</div>'); // add the actual error message under our input
                }
                if (data.errors.address) {
                    $('#address-group').addClass('has-error'); // add the error class to show red input
                    $('#address-group').append('<div class="help-block">' + data.errors.address + '</div>'); // add the actual error message under our input
                }
            } else {
                // ALL GOOD! just show the success message!
                // console.log("in submit success");
                // $('#addRsvpRowForm').append('<div class="alert alert-success">' + data.message + '</div>');
                //TODO: let the user not he succeded
                $("#addEventForm")[0].reset();
                $("#addEventForm").collapse();
                // usually after form submission, you'll want to redirect
                // window.location = '/thank-you'; // redirect a user to another page
                // alert('success'); // for now we'll just alert the user
            }
        })
        .fail(function(data) {
            // log data to the console so we can see
            $("addRowModal").modal('toggle');
            document.getElementById("errMsg").innerHTML = data.responseText;
            $("#error_modal").modal();
            console.log(data);
        });
}