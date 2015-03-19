(function ($) {
    "use strict";

    $(document).on('hidden.bs.modal', function (e) {
        $(e.target).removeData('bs.modal');
    });

    $.fn.extend({
        training_programme_tree: function (options) {
            var settings = $.extend({
                button_selectors: {create: "a.cmd_create", info: "a.cmd_view", delete: "a.cmd_delete"},
                current_node_selector: ".current_node",
                save_tree_url: '',
                save_button_id: ''
            }, options);

            var element = this;

            $("body").on("async_form-success", function (event, data) {
                $("body").trigger("training_programme-show_success", {message: data.message, type: 'success'});
            });

            $("body").on("training_programme-saved_order", function (event, data) {
                if (settings.save_button_id !== '') {
                    $("#" + settings.save_button_id).addClass('disabled');
                }
            });

            function handle_delete_buttons() {
                element.find(settings.button_selectors.delete).show();
                element.find(settings.current_node_selector).parents('li').each(function () {
                    //$(this).find("> " + settings.button_selectors.delete).css("border", "1px solid green");
                    $(this).find("> " + settings.button_selectors.delete).hide();
                });
            }

            element.on("move_node.jstree", function (event, data) {
                if (settings.save_button_id !== '') {
                    $("#" + settings.save_button_id).removeClass('disabled');
                }
                handle_delete_buttons();
            });

            element.on("loaded.jstree", function (event, data) {
                if (settings.save_button_id !== '') {
                    $("#" + settings.save_button_id).addClass('disabled');
                }

                // hmmmm ugly js workaround
                window.setTimeout(handle_delete_buttons, 500);
                //handle_delete_buttons();
            });

            $("body").on("training_programme-save_order", function () {
                var tree_data = $(element).jstree("get_json", -1, ['id']);
                var json_data = JSON.stringify(tree_data);

                if (settings.save_tree_url !== "") {
                    $.ajax({
                        url: decodeURIComponent(settings.save_tree_url),
                        type: 'post',
                        dataType: 'json',
                        data: {tree: json_data},
                        success: function (response) {
                            //try {
                            if (response) {
                                $("body").trigger("training_programme-show_success", {message: response.message, type: 'success'});
                                $("body").trigger("training_programme-saved_order");
                            }
                            /*} catch (error) {
                             console.log("The AJAX-response for the async form " + form.attr('id') + " is not JSON. Please check if the return values are set correctly: " + error);
                             }*/
                        }
                    });
                }
            });

            return element;
        },
        training_programme_modal: function (options) {
            var settings = $.extend({
                events: {hide: ["async_form-success", "async_form-cancel"]}
            }, options);

            var element = this;

            $.each(settings.events.hide, function (index, value) {
                $("body").on(value, function (data) {
                    element.modal('hide');
                });
            });

            return element;
        },
        training_programme_notifications: function (options) {
            var settings = $.extend({
                templates: {'info': '', 'success': '', 'failure': '', 'question': ''},
                events: {info: [], success: [], failure: [], question: []},
                message_var: '[MESSAGE]',
                message_delay: 3000
            }, options);

            var content_container = this;

            var displayMessage = function (event, data) {
                if (data.message) {
                    data.type = data.type || 'info';

                    var template = settings.templates[data.type];

                    if (template !== '') {
                        template = template.replace(settings.message_var, data.message);
                    }
                    $(content_container).prepend(template);
                    $('div[role="alert"]').delay(settings.message_delay).fadeOut('slow', function () {
                        $(this).remove();
                    });
                }
            };

            $.each(settings.events, function (type, events) {
                $.each(events, function (key, val) {
                    $("body").on(val, displayMessage);
                });

            });

            return content_container;
        }
    });
}(jQuery));