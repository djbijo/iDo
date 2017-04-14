$(function(){
   //handle add nickname event
    $(".msg-tag").click(function(){
        var textArea = $("#msg");
        var cursorPos = textArea.prop('selectionStart');
        var v = textArea.val();
        var textBefore = v.substring(0,  cursorPos);
        var textAfter  = v.substring(cursorPos, v.length);
        var tag = $(this).data('tag');
        textArea.val(textBefore + tag + textAfter);
        textArea.focus();
        textArea.selectionStart = cursorPos+tag.length;
    })
});