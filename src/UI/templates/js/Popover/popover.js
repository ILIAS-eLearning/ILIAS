var il = il || {};
il.UI = il.UI || {};

(function($, UI) {
    UI.popover = (function ($) {
        var show = function (triggerer_id, options) {
            var $triggerer = $('#' + triggerer_id);
            if (!$triggerer.length) {
                return;
            }
            console.log(options);
            if (!WebuiPopovers.isCreated('#' + triggerer_id)) {
                $triggerer.webuiPopover(options).webuiPopover('show');
            }
        };
        return {
            show: show
        };
    })($);
})($, il.UI);