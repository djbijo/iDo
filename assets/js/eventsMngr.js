$(function() {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.url = 'post/eventHandler.php';
    $.fn.editable.defaults.params = {action: 'update'};
    $.fn.editable.defaults.ajaxOptions = {dataType: 'json'};

    $.fn.editable.defaults.error = function(response, newValue) {
        bootbox.alert(response.responseText);
    };

    $.fn.editable.defaults.success = function(response, newValue) {
        try {
            if (response.status !== "success") {
                console.log("error");
                return response.errors.event;
            }
            getEventData();
        } catch (e){
            console.log(e);
        }
    };

    $('#EventName').editable({
        type: 'text',
        title: 'שינוי שם'
    });
    $('#EventDate').editable({
        type: 'date',
        format: 'yyyy-mm-dd',
        // format: 'YYYY-MM-DD',
        // value: "2000-01-01",
        // viewformat: 'DD/MM/YYYY',
        viewformat: 'dd/mm/yyyy',
        mode: 'popup',
        datepicker: {
            weekstart: 1
        },
        placement: 'bottom',
        title: 'שנה תאריך'
    });
    $('#EventTime').editable({
        type: 'time',
        placeholder: '19:00',
        title: 'שינוי שם'
    });
    $('#Venue').editable({
        type: 'text',
        placeholder: 'אולמי בונבון',
        title: 'שנה מקום'
    });
    $('#Address').editable({
        type: 'text',
        placeholder: '',
        title: 'שנה כתובת'
    });
});

//change event:

var getEvents = function(){
    $.ajax({
        type        : "POST",
        url         : "post/eventHandler.php",
        data        : {action: 'getEvents'},
        // contentType: "application/json; charset=utf-8",
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true,
    })
        .done( function(data){
            if (data.status === 'success'){
                console.log(data.events);
                var events = data.events;
                var numEvents = 0;
                $('#selectEventsDropdown li').remove();
                for (var prop in events){
                    numEvents++;
                    var li = $('<li></li>');
                    var a = $('<a></a>')
                    a.attr("herf", "#");
                    a.text(events[prop].name);
                    a.attr("data-event-id", events[prop].id);
                    li.append(a);
                    $("#selectEventsDropdown").append(li);
                }
                if (numEvents>1){
                    $('#change-event-group').show();
                } else {
                    $('#change-event-group').hide();
                }
                $('#selectEventsDropdown li').on("click", "a", function(event){
                    event.preventDefault();
                    changeEvent($(this).data('eventId'));
                })

            } else { //error
                if (data.errors.noEvent) {
                    $('#change-event-group').hide();
                    $("#addEventForm").collapse('show');
                }
            }
        })
        .fail(function(data){
            bootbox.alert(data.responseText);
        })
    // onclick="selectEvent(this)
};

var $eventID = 0;
var updateEventData = function(event){
    $('#EventName').editable('setValue' , event.EventName).editable('option', 'pk', event.ID)  ;
    $('#EventDate').editable('setValue' , event.EventDate, true).editable('option', 'pk', event.ID);
    document.getElementById('HebrewDate').innerHTML = event.HebrewDate;
    $('#EventTime').editable('setValue' , event.EventTime).editable('option', 'pk', event.ID);
    $('#Venue').editable('setValue' , event.Venue).editable('option', 'pk', event.ID);
    $('#Address').editable('setValue' , event.Address).editable('option', 'pk', event.ID);
    //smsgateway form:
    $('#email').val(event.Email);
    $('#password').val(event.Password);
    $('#secret').val(event.Secret);
    // $('#device-id').val = event.DeviceID;
    $("#event-data").show();
    if ($eventID !== event.ID)
        getEvents();
    $eventID = event.ID;
};

var changeEvent = function (eventId) {
    if (eventId == $eventID) return;
    $.ajax({
        type        : "POST",
        url         : "post/eventHandler.php",
        data        : {action: 'changeEvent', eventId: eventId},
        // contentType: "application/json; charset=utf-8",
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true,
        success     : function(data) {
            getEventData();
        },
        error       : function(jqXHR, status){
            console.log(status);
            console.log(jqXHR);
            bootbox.alert(jqXHR.responseText);
        }
    })
};

var getEventData = function () {
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
    console.log("loading event data");
    console.log(data);
    if (data.status === 'success'){
        // $("#addEventForm").collapse('hide');
        updateEventData(data.event);
        $("#deleteEventButton").prop('disabled', false);
    } else {
        if (data.errors.noEvent) {
            $("#event-data").hide();
            $("#deleteEventButton").prop('disabled', true);
            $('#change-event-group').hide();
            $("#addEventForm").collapse('show');
        }
    }

};
$(document).on("signedIn", getEventData);
$.when($.ready).then(function(){
    if (isSignedIn) {
        getEventData();
    }
})

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
                $("#addEventForm").collapse('hide');
                getEventData();
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

//handle remove button:
var deleteEvent = function ()  {
    bootbox.confirm("את/ה עומד/ת למחוק את האירוע לצמיתות, האם את/ה רוצה להמשיך?",function(result){
        if (result) {
            $.ajax({
                type: "POST",
                url: "post/eventHandler.php",
                data: {action: 'delete'},
                dataType: 'json', // what type of data do we expect back from the server
                encode: true,
                error: function(jqXHR, status){
                    console.log(jqXHR);
                    bootbox.alert(jqXHR.responseText);
                },
                success: (function (data) {
                    // here we will handle errors and validation messages
                    console.log(data);
                    if (data.status !== "success") {
                        //TODO: make this appear in the page
                        bootbox.alert(data.errors.delete);
                    }
                    else {
                        getEventData();
                    }
                })
            })

        }
    });
};



$("#edit-smsgateway").submit(function(event){
    // cancels the form submission
    event.preventDefault();
    submitSmsForm();
});

function submitSmsForm(){
    $('.form-group').removeClass('has-error'); // remove the error class
    $('.help-block').remove(); // remove the error text
    $('.alert-success').remove(); //remove the success text
    // Initiate Variables With Form Content
    var formData = {
        'Email' : $("#email").val(),
        'Password' : $("#password").val(),
        'Secret' : $("#secret").val(),
        'DeviceID'     : $("#device-id").val(),
    }

    $.ajax({
        type        : "POST",
        url         : "post/eventHandler.php",
        data        : {action: 'updateSms', data: formData, pk: $eventID},
        // contentType: "application/json; charset=utf-8",
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true,
    })

        .done(function(data) {
            // here we will handle errors and validation messages
            console.log(data);
            if ( data.status !== "success") {
                if (data.errors.login){
                    $('#sms-help-block').append('<div class="help-block">' + data.errors.login + '</div>');
                }
                // handle errors for name ---------------
                if (data.errors.email) {
                    $('#sms-email-group').addClass('has-error'); // add the error class to show red input
                    $('#sms-email-group').append('<div class="help-block">' + data.errors.email + '</div>'); // add the actual error message under our input
                }
                if (data.errors.secret) {
                    $('#sms-secret-group').addClass('has-error'); // add the error class to show red input
                    $('#sms-secret-group').append('<div class="help-block">' + data.errors.secret + '</div>'); // add the actual error message under our input
                }
                // if (data.errors.deviceId) {
                //     $('#sms-id-group').addClass('has-error'); // add the error class to show red input
                //     $('#sms-id-group').append('<div class="help-block">' + data.errors.deviceId + '</div>'); // add the actual error message under our input
                // }
                if (data.errors.password) {
                    $('#sms-password-group').addClass('has-error'); // add the error class to show red input
                    $('#sms-password-group').append('<div class="help-block">' + data.errors.password + '</div>'); // add the actual error message under our input
                }
            } else {
                // ALL GOOD! just show the success message!
                // console.log("in submit success");
                $('#edit-smsgateway').append('<div class="alert alert-success">' + data.msg + '</div>');
                //TODO: let the user not he succeded
                // $("#edit-smsgateway")[0].reset();
                // $("#edit-smsgateway").collapse();
                // getEventData();
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