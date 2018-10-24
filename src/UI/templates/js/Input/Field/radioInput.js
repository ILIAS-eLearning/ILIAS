/**
 * Provides behavior of dependant groups for radio-options.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, UI) {

    il.UI.Input.radio = (function ($) {
        var init = function (id) {
            var id = '#' + id,
                options = $(id).find('input:radio');

            options.change(function(){_select(id);});
            _hidegroups(id);
        };

        var _current = function (id) {
            return $(id).children('.il-input-radiooption').children('input:radio:checked').val();
        }

        var _hidegroups = function (id) {
            var current = _current(id),
                visible_id = id.substr(1) + '_' + current + '_group';

            $(id).children('.il-input-radiooption').children('.il-dependant-group').each(
                function(i, group) {
                    if(group.id !== visible_id) {
                        $(group).hide();
                    }
                }
            );
        }
        var _select = function (id) {
            var value = _current(id),
                group_id = id + '_' + value + '_group';

            _hidegroups(id);
             $(group_id).show();
        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);