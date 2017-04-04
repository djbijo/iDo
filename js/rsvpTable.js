var $table = $('#table')
$button = $('#button');

$(function () {
    $table.bootstrapTable({
        url: 'json/rsvp.json',
        toolbar: '#toolbar',
        idField: 'id',
        showColumns: true,
        mobileResponsive: true,
        resizable: true,
        showToggle: true,
        search: true,
        columns: [
            {
                formatter: function(value, row, index) {
                    return index;
                },
                title: "מס"
            },
            {
                field: 'id',
                title: 'מס"ד',
                visible: false,
                switchable: false
            },  {
                field: 'name',
                title: 'שם',
                editable: {
                    type: 'text',
                    url: 'tableUpdate.php',
                    title: 'name',
                    success: function (response, newValue) {
                        console.log(response);
                        respArr = JSON.parse(response);
                        console.log(respArr);
                        if (!respArr.success)
                            return respArr.msg;
                    }
                }
            }, {
                field: 'surname',
                title: 'שם משפחה',
                editable: {
                    type: 'text',
                    url: '/post',
                    title: 'surname'
                }
            }]
    })
});
//    $(function () {
//        var data = $.getJSON('json/rsvp.json');
//        [
//            {
//                "id"       : 0,
//                "name"     : "ישראל",
//                "surname"  : "ישראלי",
//                "nick"     : "שרול",
//                "phone"    : "052-555",
//                "email"    : "israel@israeli.com",
//                "groups"   : "חברים",
//                "invitees" : 7,
//                "rsvp"     : 2,
//                "maybe"    : 1,
//                "ride"     : "כן"
//            }
//        ];
//        $table.bootstrapTable({data: data});
//    });
$(function () {
    $('#toolbar').find('select').change(function () {
        $table.bootstrapTable('destroy').bootstrapTable({
            exportDataType: $(this).val()
        });
    });
})

// $(function () {
//     $button.click(function () {
//         var randomId = 100 + ~~(Math.random() * 100);
//         $table.bootstrapTable('insertRow', {
//             index: 1,
//             row: {
//                 "id"       : 0,
//                 "name"     : "ישראל",
//                 "surname"  : "ישראלי",
//                 "nick"     : "שרול",
//                 "phone"    : "052-555",
//                 "email"    : "israel@israeli.com",
//                 "groups"   : "חברים",
//                 "invitees" : 7,
//                 "rsvp"     : 2,
//                 "maybe"    : 1,
//                 "ride"     : "כן"
//             }
//         });
//     });
// });
$.mockjax({
    url: '/post',
    responseTime: 400,
//        status: 200,
    response: function(settings) {
//            console.log(settings);
        if(settings.data.value == 'err') {
            this.status = 500;
            this.responseText = {
                success: false,
                msg: "not good, not good"
            }
        } else {
            this.responseText = {
                success: true
            };
        }
        this.data = "something";
    }
});


$("#addRsvpRowForm").submit(function(event){
    // cancels the form submission
    event.preventDefault();
    console.log("in submit");
    submitForm();
});

function submitForm(){
    // Initiate Variables With Form Content
    var formData = {
        'name'     : $("#InputName").val(),
        'surname'  : $("#InputSurName").val(),
        'nickName' : $("#InputNickName").val(),
        'invitees' : $("#InputInvitees").val(),
        'phone'    : $("#InputPhone").val(),
        'email'    : $("#InputEmail").val(),
        'groups'   : $("#InputGroups").val(),
        'rsvp'     : $("#InputRsvp").val(),
        'ride'     : $("#InputRide").val(),
    }

    $.ajax({
        type        : "POST",
        url         : "post/rsvpAddRow.php",
        data        : formData,
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true,
    })

    .done(function(data) {
        // here we will handle errors and validation messages
        if ( ! data.success) {
            // handle errors for name ---------------
            if (data.errors.name) {
                $('#name-group').addClass('has-error'); // add the error class to show red input
                $('#name-group').append('<div class="help-block">' + data.errors.name + '</div>'); // add the actual error message under our input
            }
            if (data.errors.usr) {
                $('#addRsvpRowForm').addClass('has-error'); // add the error class to show red input
                $('#addRsvpRowForm').append('<div class="help-block">' + data.errors.usr + '</div>'); // add the actual error message under our input
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
            console.log("in submit success");
            $('#addRsvpRowForm').append('<div class="alert alert-success">' + data.message + '</div>');
            $("#addRsvpRowForm")[0].reset();
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
function formSuccess(){
    $("#addRsvpRowForm")[0].reset();
}