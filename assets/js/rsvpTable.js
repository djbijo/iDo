var $table = $('#table')
$button = $('#button');

$(function () {
    $table.bootstrapTable({
        // url: 'post/rsvpGet.php',
        toolbar: '#toolbar',
        idField: 'ID',
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
                field: 'ID',
                title: 'ID',
                visible: true,
                switchable: false
            },  {
                field: 'Name',
                title: 'שם',
                editable: {
                    type: 'text',
                    url: 'post/rsvpCellUpdate.php',
                    title: 'name',
                    dataType: "json",
                    success: function (response, newValue) {
                        console.log(response);
                        try {
                            respArr = JSON.parse(response);
                        } catch (err) {
                            document.getElementById("errMsg").innerHTML = response;
                            $("#error_modal").modal();
                        }
                        console.log(respArr);
                        if (respArr.success == false)
                            return respArr.msg;
                    },
                    fail: function (data){
                        console.log("failed");
                    }
                }
            }, {
                field: 'Surname',
                title: 'שם משפחה',
                editable: {
                    type: 'text',
                    url: '/post',
                    title: 'surname'
                }
            }]
    })
});
   $(function () {
       $.ajax({
           type        : "POST",
           url         : "post/rsvpGet.php",
           data        : {},
           contentType: "application/json; charset=utf-8",
           dataType    : 'json', // what type of data do we expect back from the server
           encode      : true
       })
       .done(function(data) {
           // here we will handle errors and validation messages
           console.log(data);
           if ( ! data.success) {
               document.getElementById("errMsg").innerHTML = data.error;
               $("#error_modal").modal();
           }
           else {
               console.log("got table data success");
               $table.bootstrapTable('load', (data.table));
           }
       })
       .fail(function(data) {
           // log data to the console so we can see
           document.getElementById("errMsg").innerHTML = data.responseText;
           $("#error_modal").modal();
           console.log(data);
       });
   });
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
// $.mockjax({
//     url: '/post',
//     responseTime: 400,
// //        status: 200,
//     response: function(settings) {
// //            console.log(settings);
//         if(settings.data.value == 'err') {
//             this.status = 500;
//             this.responseText = {
//                 success: false,
//                 msg: "not good, not good"
//             }
//         } else {
//             this.responseText = {
//                 success: true
//             };
//         }
//         this.data = "something";
//     }
// });


$("#addRsvpRowForm").submit(function(event){
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
        url         : "post/rsvpAddRow.php",
        data        : formData,
        dataType    : 'json', // what type of data do we expect back from the server
        encode      : true
    })

    .done(function(data) {
        // here we will handle errors and validation messages
        console.log(data);
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
            // console.log("in submit success");
            // $('#addRsvpRowForm').append('<div class="alert alert-success">' + data.message + '</div>');
            //TODO: let the user not he succeded
            $("#addRsvpRowForm")[0].reset();
            $("#addRowModal").modal('toggle');
            $table.bootstrapTable('append', formData);
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