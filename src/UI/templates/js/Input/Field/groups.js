/**
 * Changes visibility of (sub)items of groups
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
il.UI.Input.groups = il.UI.Input.groups || {};
(function () {

    il.UI.Input.groups.optional = (function ($)
    {
        var init = function (id) {
            var control = $('#' + id);
            control.change(onchange)
            control.change()
        }

        var onchange = function () {
            var control = $(this),
                group = control.siblings(".form-group");

            if(control[0].checked) {
                group.show();
            } else {
                group.hide();
            }
        };

        return {
            init: init
        };

    })($);

    il.UI.Input.groups.switchable = (function ($)
    {
        var init = function (id) {
            var control = $('#' + id);
            control.change(onchange)
            control.change()
        }

        var onchange = function () {
            var control = $(this),
                options = control.children('.il-input-radiooption').children('input');

            options.each(function(index, opt) {
                var group = $(opt).siblings('.form-group');
                if(opt.checked) {
                    group.show();
                } else {
                    group.hide();
                }
            });
        };

        return {
            init: init
        };

    })($);
})();
