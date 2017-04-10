
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
    if (data.status == 'success'){
        console.log("received event data");
        $('#home').append("<p>"+JSON.stringify(data.event, null , 4)+"</p>");
    } else {
        console.log(data);
    }

};
$(document).on("signedIn", getEventData);

