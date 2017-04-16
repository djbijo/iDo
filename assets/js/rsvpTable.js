var $rsvpTable = $('#table')
$removeButton = $('#RemoveButton')
$refreshButton = $('#RefreshButton')
$rsvpTableLoaded = false;

$(function () {
    $rsvpTable.bootstrapTable({
        // url: 'post/rsvpGet.php',
        toolbar: '#toolbar',
        idField: 'ID',
        showColumns: true,
        mobileResponsive: true,
        resizable: true,
        showToggle: true,
        search: true,
        striped: true,
        showRefresh: true,
        detailView: true,
        onRefresh: function () {
            $rsvpTableLoaded = false;
            loadRsvpTableData();
        },
        columns: [
            {
                formatter: function(value, row, index) {
                    return index;
                },
                title: "מס"
            },
            {
                field: 'ID',
                title: 'ID',
                visible: false,
                switchable: false
            }, {
                field: 'check',
                checkbox: true
            }, {
                field: 'Name',
                title: 'שם',
                sortable: true,
                editable: ezMakeEditable('text', 'שם')
            }, {
                field: 'Surname',
                title: 'שם משפחה',
                sortable: true,
                editable: ezMakeEditable('text', 'שם משפחה')
            },
            {
                field: 'Email',
                title: 'email',
                sortable: true,
                editable: ezMakeEditable('email', 'email')
            },
            {
                field: 'Groups',
                title: 'קבוצות',
                sortable: true,
                editable: ezMakeEditable('text', 'קבוצות') //TODO: enable groups select
                // editable: ezMakeEditable('checklist', 'קבוצות')
            },
            {
                field: 'Invitees',
                title: 'מוזמנים',
                sortable: true,
                editable: ezMakeEditable('number', 'מוזמנים')
            },
            {
                field: 'Nickname',
                title: 'כינוי',
                sortable: true,
                editable: ezMakeEditable('text', 'כינוי')
            },
            {
                field: 'Phone',
                title: 'טלפון',
                sortable: true,
                editable: ezMakeEditable('tel', 'טלפון')
            },
            {
                field: 'RSVP',
                title: 'אישרו הגעה',
                sortable: true,
                editable: ezMakeEditable('number', 'אישרו הגעה')
            },
            {
                field: 'Uncertain',
                title: 'מתלבטים',
                sortable: true,
                editable: ezMakeEditable('text', 'מתלבטים')
            },
            {
                field: 'Ride',
                title: 'הסעה',
                sortable: true,
                editable: { //FIXME: need to find a way to make it simple checkbox
                    type: 'checklist',
                    value: 0,
                    source: [
                        // {value: false, text: 'אין הסעה'},
                        {value: 1, text: 'יש הסעה'}
                    ],
                    mode: 'inline',
                    url: 'post/rsvpCellUpdate.php',
                    dataType: "json",
                    success: cellUpdateSuccess,
                    highlight: '#8400F1',

                    // toggle: 'mouseenter'
                }
            }
            ]
    })
});

//table actions:
$rsvpTable.on('expand-row.bs.table', function (e, index, row, $detail) {
        $detail.bootstrapTable({
            columns: [
                {
                field: 'Message',
                title: 'תוכן ההודעה'
                }, {
                field: 'Received',
                title: 'התקבל'
                }],
            url: 'post/rsvpHandler.php',
            ajaxOptions: function(params){
                return {action: 'getRawData', phone: row.Phone}
            }

        })
        // $detail.html('Loading from ajax request...');
        // $.get('post/rsvpHandler.php',
        //     {action: 'getRawData', phone: row.Phone}, null,'json')
        //     .done( function (res){
        //         console.log(res);
        //         if (res.status == "success") {
        //             // $detail.html(res.status);
        //             $detail.bootstrapTable(res.data);
        //         }
        //         else
        //             $detail.html("fail");
        //     });
});

function cellUpdateSuccess(response, newValue){
    var respArr = response;
    // try {
    //     respArr = JSON.parse(response);
    // } catch (err) {
    //     document.getElementById("errMsg").innerHTML = response;
    //     $("#error_modal").modal();
    // }
    if (respArr.status === 'error'){
        return respArr.error;
    }
}

function ezMakeEditable(type, title){
    return {
        type: type,
        url: 'post/rsvpHandler.php',
        params: {action: 'cellUpdate'},
        title: title,
        dataType: "json",
        success: cellUpdateSuccess,
        highlight: '#8400F1'
        // toggle: 'mouseenter'
    }
}
var loadRsvpTableData = function () {
    if (!isSignedIn || $rsvpTableLoaded)
        return;
   $.ajax({
       type        : "POST",
       url         : "post/rsvpHandler.php",
       data        : {action: 'getTable'},
       // contentType: "application/json; charset=utf-8",
       dataType    : 'json', // what type of data do we expect back from the server
       encode      : true
   })
   .done(function(data) {
       // here we will handle errors and validation messages
       console.log(data);
       if (data.status !== 'success') {
           document.getElementById("errMsg").innerHTML = data.error;
           $("#error_modal").modal();
       }
       else {
           console.log("got table data success");
           $rsvpTable.bootstrapTable('load', (data.table));
           rsvpTableLoaded = true;
           $rsvpTable.bootstrapTable('hideLoading');
       }
   })
   .fail(function(data) {
       // log data to the console so we can see
       document.getElementById("errMsg").innerHTML = data.responseText;
       $("#error_modal").modal();
       console.log(data);
   });
};
$(document).on("signedIn", loadRsvpTableData);
$(function(){
    $rsvpTable.bootstrapTable('showLoading');
    loadRsvpTableData();
});

$(function () {
    $('#toolbar').find('select').change(function () {
        $rsvpTable.bootstrapTable('destroy').bootstrapTable({
            exportDataType: $(this).val()
        });
    });
})

$("#addRsvpRowForm").submit(function(event){
    // cancels the form submission
    event.preventDefault();
    submitForm();
});

function submitForm(){
    $('.form-group').removeClass('has-error'); // remove the error class
    $('.help-block').remove(); // remove the error text
    $('.alert-success').remove(); //remove the success text
    // Initiate Variables With Form Content
    var formData = {
        'Name'     : $("#InputName").val(),
        'Surname'  : $("#InputSurName").val(),
        'NickName' : $("#InputNickName").val(),
        'Invitees' : $("#InputInvitees").val(),
        'Phone'    : $("#InputPhone").val(),
        'Email'    : $("#InputEmail").val(),
        'Groups'   : $("#InputGroups").val(),
        'Rsvp'     : $("#InputRsvp").val(),
        'Ride'     : $("#InputRide").val(),
    }

    $.ajax({
        type        : "POST",
        url         : "post/rsvpHandler.php",
        data        : {action: 'addRow', data: formData},
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true
    })

    .done(function(data) {
        // here we will handle errors and validation messages
        console.log(data);
        if ( data.status !== "success") {
            // handle errors for name ---------------
            if (data.errors.Name) {
                $('#name-group').addClass('has-error'); // add the error class to show red input
                $('#name-group').append('<div class="help-block">' + data.errors.Name + '</div>'); // add the actual error message under our input
            }
            if (data.errors.Surname) {
                $('#surname-group').addClass('has-error'); // add the error class to show red input
                $('#surname-group').append('<div class="help-block">' + data.errors.Surname + '</div>'); // add the actual error message under our input
            }
            if (data.errors.Invitees) {
                $('#invitees-group').addClass('has-error'); // add the error class to show red input
                $('#invitees-group').append('<div class="help-block">' + data.errors.Invitees + '</div>'); // add the actual error message under our input
            }
            if (data.errors.Phone){
                $('#phone-group').addClass('has-error'); // add the error class to show red input
                $('#phone-group').append('<div class="help-block">' + data.errors.Phone + '</div>'); // add the actual error message under our input
            }
            // handle errors for email ---------------
            // if (data.errors.phone) {
            //     $('#phone-group').addClass('has-error'); // add the error class to show red input
            //     $('#phone-group').append('<div class="help-block">' + data.errors.phone + '</div>'); // add the actual error message under our input
            // }
            // // handle errors for superhero alias ---------------
            // if (data.errors.message) {
            //     $('#message-group').addClass('has-error'); // add the error class to show red input
            //     $('#message-group').append('<div class="help-block">' + data.errors.message + '</div>'); // add the actual error message under our input
            // }
        } else {
            // ALL GOOD! just show the success message!
            // console.log("in submit success");
            // $('#addRsvpRowForm').append('<div class="alert alert-success">' + data.message + '</div>');
            //TODO: let the user not he succeded
            $("#addRsvpRowForm")[0].reset();
            $("#addRowModal").modal('toggle');
            formData.ID = data.ID;
            $rsvpTable.bootstrapTable('append', formData);
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
$(function () {
    $removeButton.click(function () {

        var ids = $.map($rsvpTable.bootstrapTable('getSelections'), function (row) {
            return row.ID;
        });
        if (ids.length == 0){
            //todo: tell the user he needs to choose somthing
            bootbox.alert("צריך לבחור שורות למחיקה");
            return;
        }
        bootbox.confirm("את/ה עומד/ת למחוק את השורות לצמיתות, האם את/ה רוצה להמשיך?",function(result){
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "post/rsvpHandler.php",
                    data: {action: 'deleteRows', ids: ids},
                    dataType: 'json', // what type of data do we expect back from the server
                    encode: true,
                    error: function(jqXHR, status){
                        console.log(status);
                        console.log(jqXHR);
                        bootbox.alert(jqXHR.responseText);
                    },
                    success: (function (data) {
                        // here we will handle errors and validation messages
                        console.log(data);
                        if (data.status === "success") {
                            $rsvpTable.bootstrapTable('remove',
                                {
                                    field: 'ID',
                                    values: ids
                                });
                        }
                        else {
                            //TODO: error
                        }
                    })
                })

            }
        });
        // $rsvpTable.bootstrapTable('remove', {
        //     field: 'id',
        //     values: ids
        // });
    });
});