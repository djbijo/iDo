var ezVite = {
    updateRawData(){
        $.get('post/rowDataHandler.php', {action: "updateFromServer"}, null, "json")
            .done(function (res) {
                if (res.status === 'success'){
                    console.log("received "+res.newMsgs+" new messages");
                } else {
                    console.log(res);
                    if (res.errors) {
                        if (res.errors.get) {
                            console.log(res.errors.get);
                        } else {
                            for (var prop in res.errors) {
                                bootbox.alert(prop + " : " + res.errors[prop])
                                break;
                            }
                        }
                    } else {
                        bootbox.alert(res);
                    }
                }
            })
            .fail(function (res) {
               console.log(res.responseText);
               bootbox.alert("Error: "+res.responseText);
            });
    },

    //taken from http://stackoverflow.com/questions/22581345/click-button-copy-to-clipboard-using-jquery
    copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
    }
};