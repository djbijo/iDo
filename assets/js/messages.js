$(function(){
   //handle add tag event
    $(".msg-tag").click(function(){
        var textArea = $("#msg");
        var cursorPos = textArea.prop('selectionStart');
        var v = textArea.val();
        var textBefore = v.substring(0,  cursorPos);
        var textAfter  = v.substring(cursorPos, v.length);
        var tag = $(this).data('tag');
        textArea.val(textBefore + tag + textAfter);
        textArea.focus();
        // textArea.selectionStart = cursorPos+tag.length;
    });

    $('input[type="time"][value="now"]').each(function() {
        //FIXME: need to initialize +5 minutes or something
        var d = new Date(),
            h = d.getHours(),
            m = d.getMinutes();
        if (h < 10) h = '0' + h;
        if (m < 10) m = '0' + m;
        $(this).attr({
            'value': h + ':' + m
        });
    });

    $('input[type="date"][value="now"]').each(function() {
        var d = new Date(),
            date = d.getDate(),
            month = d.getMonth(),
            year = d.getFullYear();
        if (date<10) date = '0' + date;
        if (month<10) month = '0' + month;
        $(this).attr({
            'value': year+'-'+month+'-'+date
        });
    });

    $("#msgSave").click(function (event) {
        event.preventDefault();
        handleMsg(false);
    });

    $("#msgSend").click(function (event) {
        event.preventDefault();
        handleMsg(true);
    });

    let handleMsg = function (send) {
        var msg = $("#msg").val(),
         time = $("#msg-send-time").val(),
         date = $("#msg-send-date").val(),
         action = send ? 'send' : 'add';

        console.log("msg is: " + msg + " date to send: " + date+" time: "+time);
        $.ajax({
            type        : "POST",
            url         : "post/messagesHandler.php",
            data        : {
                action : action,
                message: msg,
                date   : date,
                time   : time
            },
            // contentType: "application/json; charset=utf-8",
            dataType    : 'json', // what type of data do we expect back from the server
            encode      : true,
        })

        .done(function(data) {
            console.log("sent msg success");
            console.log(data);
        })
        .fail(function (data) {
            console.log(data);
        })
    }

    var $msgTable = $("#messages-table");
    $msgTable.bootstrapTable({
        url: 'post/messagesHandler.php',
        method: "POST",
        queryParams: {action: 'get'},
        ajax: function(params){
            $.ajax({
                type        : "POST",
                url         : "post/messagesHandler.php",
                data        : {
                    action : 'get'
                },
                // contentType: "application/json; charset=utf-8",
                dataType    : 'json', // what type of data do we expect back from the server
                encode      : true,
            })
            .done(function(data){
                params.success(data);
            })
        },
        responseHandler: function (res) {
            console.log("in get table");
            console.log(res);
            if(res['status']==='success')
                return res['table'];
            else return null;
        },
        // toolbar: '#toolbar',
        idField: 'ID',
        showColumns: true,
        mobileResponsive: true,
        resizable: true,
        showToggle: true,
        search: true,
        striped: true,
        showRefresh: true,
        // onRefresh: function () {
        //     $rsvpTableLoaded = false;
        //     loadRsvpTableData();
        // },
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
                field: 'Message',
                title: 'הודעה',
                sortable: true,
            }
        ]
    })
});