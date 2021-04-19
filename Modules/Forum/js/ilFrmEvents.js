/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

il.FrmEvents = {
    ajax_url: '',

    init: function (url) {
        this.ajax_url = url;

        $('form[id*="frmevents_"]').submit(function(event) {
            var form_id = $(this).attr("id");
            var form_id_parts = form_id.split("_");
            var notification_id = form_id_parts[0];
            var modal_id = form_id_parts[0] + "_" + form_id_parts[1];

            if(notification_id)	{

                $("#" + modal_id).modal("hide");
                var events = $('#frmevents_'+notification_id).val();

                $.ajax({
                    url: il.FrmEvents.ajax_url,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        notification_id: notification_id,
                        events: events
                    },
                    success: function (response) {
                        $("#"+form_id).html(response);

                    }
                }).fail(function() {

                });
            }

            event.preventDefault();
        });
    },

    showEvents: function (id) {
        $("#" + id).modal('show');
        return false;
    }
}