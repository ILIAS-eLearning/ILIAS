var NotificationItemTests = {
	html: "NotificationItem/NotificationItem.html",

	testGetValidObject: function(){
		return (!(getNotificationItemTest1() instanceof jQuery));
	},
	testGetCloseButton1: function(){
		$button = getNotificationItemTest1().getCloseButtonOfItem();
		return ($button instanceof jQuery);
	},
	testGetCloseButton2: function(){
		$button = getNotificationItemTest1().getCloseButtonOfItem();
		return ($button.attr('id') == "close_button_id");
	},
	getCounterObjectIfAny1: function(){
		$counter = getNotificationItemTest1().getCounterObjectIfAny();
		return $counter.getNoveltyCount() == 1 && $counter.getStatusCount()==0;
	}
};

var getNotificationItemTest1 = function(){

	return il.UI.item.notification.getNotificationItemObject($("#notification_item_id"));
};
