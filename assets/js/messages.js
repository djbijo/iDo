$(function(){
   //handle add nickname event
    $(".msg-tag").click(function(){
        var cursorPos = $('#msg').prop('selectionStart');
        var v = $('#msg').val();
        var textBefore = v.substring(0,  cursorPos);
        var textAfter  = v.substring(cursorPos, v.length);

        $('#msg').val(textBefore + $(this).data('tag') + textAfter);
        // $("#msg").append('{כינוי}');
    })
});