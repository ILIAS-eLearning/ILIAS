var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.popover = (function ($) {

        var toggle = function ($triggerer, options) {
            options = JSON.parse(options);
            $triggerer.popover(options).popover('toggle');
        };

        return {
            toggle: toggle
        };

    })($);

})($, il.UI);