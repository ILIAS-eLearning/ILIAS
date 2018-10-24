/**
 * Provides the behavior of all dependant Groups.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, UI) {
    il.UI.Input.dependantGroup = (function ($) {
        /**
         * Initializes a given dependant Dropzone
         *
         * @param {string} type the type of the dropzone
         *                      MUST be the full qualified class name.
         * @param {Object} options possible settings for this dropzone
         */
        var init = function (id, signals) {
            id = '#'+id;
            $(document).on(signals.show, function(signal,params) { $(id).show();});
            $(document).on(signals.hide, function(signal,params) { $(id).hide();});
            $(document).on(signals.toggle, function(signal,params) { $(id).toggle();});
            $(document).on(signals.init, function(signal,params) {
                $(id).toggle(params.triggerer[0].checked);
            });

        };

        return {
            init: init
        };

    })($);

    il.UI.Input.radio = (function ($) {
        var init = function (id) {
            var id = '#' + id,
                options = $(id).children('input:radio');
            options.change(function(){_select(id);});
            _hidegroups(id);
        };

        var _current = function (id) {
            return $(id).children('input:radio:checked').val()
        }
        var _hidegroups = function (id) {
            var current = _current(id),
                visible_id = id.substr(1) + '_' + current + '_group';
            $(id).children('.il-dependant-group').each(function(i, group) {
                if(group.id !== visible_id) {
                    $(group).hide();
                }
            });
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