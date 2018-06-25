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
})($, il.UI.Input);