
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href"); // activated tab
    console.log("target is " + target);
    $.ajaxSetup({
        cache: false
    });
    switch (target) {
        case "#rsvp":
            $(target).load("Views/RSVPTable.html");
            break;
        case "#home":
            break;
        case "#events":
            $(target).load("Views/events.html");
            break;
        default:
            $("#error_modal").modal();
    }

//        alert(target);
});

/////////////////////////////////////
// the code below is used to save tab on refresh and direct access to tab via hash
if (location.hash) {
    $('a[href=\'' + location.hash + '\']').tab('show');
}
var activeTab = localStorage.getItem('activeTab');
if (activeTab) {
    $('a[href="' + activeTab + '"]').tab('show');
}

$('body').on('click', 'a[data-toggle=\'tab\']', function (e) {
    e.preventDefault();
    var tab_name = this.getAttribute('href');
    if (history.pushState) {
        history.pushState(null, null, tab_name)
    }
    else {
        location.hash = tab_name
    }
    localStorage.setItem('activeTab', tab_name);

    $(this).tab('show');
    return false;
});
$(window).on('popstate', function () {
    var anchor = location.hash ||
        $('a[data-toggle=\'tab\']').first().attr('href');
    $('a[href=\'' + anchor + '\']').tab('show');
});