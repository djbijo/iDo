$(function(){
    var $msgTable = $("#messages-table");
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
        var data = getDataFromForm();
        handleMsg('update', data);
    });

    $("#msgSend").click(function (event) {
        event.preventDefault();
        var data = getDataFromForm();
        handleMsg('updateSend', data);
    });

    function getDataFromForm(){
        var data = {
            message : $("#msg").val(),
            date: $("#msg-send-date").val(),
            time: $("#msg-send-time").val(),
            id  : $("#msg").data('id')
        };
        return data;
    }

    let handleMsg = function (action, data) {

        $.ajax({
            type        : "POST",
            url         : "post/messagesHandler.php",
            data        : {
                action : action,
                data: data
            },
            dataType    : 'json', // what type of data do we expect back from the server
            encode      : true,
        })

        .done(function(data) {
            if (data.status==='success') {
                console.log("sent msg success");
                $msgTable.bootstrapTable('refresh');
            }
            else {
                console.log(data.errors);
                if (data.sendMsg) bootbox.alert(data.sendMsg);
            }
        })
        .fail(function (data) {
            console.log(data);
            bootbox.alert(data.responseText);
        })
    }

    window.operateEvents = {
        'click .like': function (e, value, row, index) {
            alert('You click like action, row: ' + JSON.stringify(row));
            //send

        },
        'click .remove': function (e, value, row, index) {
            //file form:
            $("#msg-send-date").val(row.SendDate);
            $("#msg-send-time").val(row.SendTime);
            $("#msg").val(row.Message);
            $("#msg").data('id', row.ID);
            //todo: remove row
            // $msgsTable.bootstrapTable('remove', {
            //     field: 'ID',
            //     values: [row.ID]
            // });
        }
    };

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
            }, {
                field: 'operate',
                title: 'Item Operate',
                align: 'center',
                events: operateEvents,
                formatter: operateFormatter
            }
        ]
    })
    function operateFormatter(value, row, index) {
        return [
            '<a class="like" href="javascript:void(0)" title="Like">',
            '<i class="glyphicon glyphicon-heart"></i>',
            '</a>  ',
            '<a class="remove" href="javascript:void(0)" title="Remove">',
            '<i class="glyphicon glyphicon-remove"></i>',
            '</a>'
        ].join('');
    }


});

