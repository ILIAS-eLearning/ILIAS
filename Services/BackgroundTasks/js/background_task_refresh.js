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

        var updateDescriptionOnClose = function (id, description_text) {
            var notification_item = il.UI.item.notification.getNotificationItemObject($(id));
            var parent_item = notification_item.getParentItem();

            notification_item.getCloseButtonOfItem().click(function () {
                parent_item.setItemDescription(notification_item.getNrOfSibblings() + " " + description_text);
            });
        };

        return {
            refreshItem: refreshItem,
            updateDescriptionOnClose: updateDescriptionOnClose
        };
    })($);
})($, il);




