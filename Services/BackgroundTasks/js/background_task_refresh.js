$(document).ready(function () {
    var glyph = $(".mm_background_tasks > a");
    var refresh_uri = $(".mm_background_tasks").data('refreshUri');

    var refresh = function () {
        var popover_content = $(".bt-popover-content");
        var popover_container = popover_content.parent();

        if (popover_content.length > 0 && popover_container.length > 0 && popover_content.is(":visible") && popover_container.is(":visible")) {
            $.ajax({
                url: refresh_uri,
                type: 'GET',

                success: function (data) {
                    var btt = $(data).attr('background-tasks-total');
                    var btui = $(data).attr('background-tasks-user-interaction');

                    glyph.find(".il-counter-novelty").html(btui);
                    glyph.find(".il-counter-status").html(btt - btui);

                    popover_content.replaceWith(data);
                }
            });
        }
        // do some stuff
        setTimeout(arguments.callee, 2000);
    };

    setTimeout(refresh, 2000);
});
