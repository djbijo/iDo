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
        var msg = $("#msg").val();
        var time = $("#msg-send-time").val();
        var date = $("#msg-send-date").val();
        console.log("msg is: " + msg + " date to send: " + date+" time: "+time);
    }
});