il = il || {};
il.BGTask = il.BGTask || {};
(function ($, il) {
    il.BGTask = (function ($) {
        var refreshments = 0;

        var refreshItem = function (notification_item, url) {
            setTimeout(function () {
                console.log("Item has been refreshed: " + refreshments++);
                //@TODO, when do we need to replace content?
                if (true) {
                    notification_item.replaceByAsyncItem(url, {refreshes: refreshments});
                }
                // do some stuff
            }, 2000);
        };

        return {
            refreshItem: refreshItem
        };
    })($);
})($, il);




