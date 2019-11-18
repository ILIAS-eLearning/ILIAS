il = il || {};
il.UI = il.UI || {};
il.UI.item = il.UI.item || {};

/**
 * Scope for JS code for the Items in the UI Components.
 *
 * This Scope offers an interface providing two function for the Notification Items
 * that are currently only used internally while rendering the Notification Items
 */
(function($, item ) {
	item.notification = (function($) {
		/**
		 * Name of the counter class in the DOM
		 * @private
		 */
		var _cls_item_container = 'il-item-notification-replacement-container';

		/**
		 * See Interface description
		 */
		var getNotificationItemObject= function($item_or_object_inside_item){
			console.assert($item_or_object_inside_item instanceof jQuery,
				"$item_or_object_inside_item is not a jQuery Object, param: "+$item_or_object_inside_item);

			var $item = $item_or_object_inside_item;
			if(!$item.hasClass(_cls_item_container)){
				$item = $item_or_object_inside_item.closest("."+_cls_item_container);
			}
			console.assert($item.length > 0, "Passed jQuery Object does not contain a Notification Item");

			//Make sure *this* in generateCounterObject is properly bound.
			var NotificationItemConstructor = generateNotificationItemObject.bind({});
			return NotificationItemConstructor($item);
		};

		/**
		 * Interface returned by this function for public use (see return statement bellow)
		 * The contained functions are implemented bellow
		 */
		var public_interface = {

			/**
			 * The argument passed mussed be the jQuery Object of some element containing
			 * a notification slate. Then, the function searches the jQuery Notification Slate
			 * object in the DOM and creates an new Notification Slate object by using the
			 * generateNotificationSlateObject function
			 */

			getNotificationItemObject: getNotificationItemObject,
		};



		/**
		 * Declaration and implementation of the notification slate object
		 */
		var generateNotificationItemObject = function($item){
			var $item = $item;

			/**
			 * See Interface description
			 * @param id
			 */
			this.registerAggregates = function(prevent_toggle){
				var $aggregates = getAggregatesOfItem().hide();

				$aggregates.find(".il-maincontrols-slate-notification-title").click(function(){
					disEngageAggregatesOfItem($aggregates)
				});

				if(!prevent_toggle){
					var $title = $item.find(".il-item-notification-title").first();
					$title.find("a").attr("href", "#");
					$title.click(function(event){
						engageAggregatesOfItem($aggregates);
					});
				}
			};



			/**
			 * See Interface description
			 * @param id
			 */
			this.replaceByAsyncItem = function(url,send_data){
				disEngageAggregatesOfItem(getAggregatesOfItem());
				getAggregatesOfItem().remove();
				performAsyncCall(url,send_data,function(data) {
					getParentSlateOfItem().show();
					$item.html(data);
				});
				return this;
			};

			this.replaceContentByAsyncItemContent = function(url,send_data){
				performAsyncCall(url,send_data,function(data) {
					copyContent($item,$(data),[
						".il-item-notification-title",
						".il-item-additional-content",
						".il-item-properties",
						".il-item-description"]);
				});
				return this;
			};

			this.addAsyncAggregate = function(url,send_data){
				var self = this;
				performAsyncCall(url,send_data,function(data) {
					var $aggregates = getAggregatesOfItem().append(data);
					if($aggregates.find(".il-item-notification-replacement-container").length === 1){
						self.registerAggregates();
					}
				});
				return this;
			};

			this.getCounterObjectIfAny = function(){
				var $meta_bar = getMetaBarOfItemIfIsInOne();
				if($meta_bar.length){
					return il.UI.counter.getCounterObject(getNotificationsTriggererIfAny());
				}
			}

			/**
			 * See Interface description
			 * @param id
			 * @param url
			 */
			this.registerCloseAction = function(url,amount) {
				var self = this;
				var $close_button = this.getCloseButtonOfItem();
				if($close_button.length){
					$close_button.click(function(){
						var $counter = self.getCounterObjectIfAny();
						if($counter){
							$counter.decrementNoveltyCount(amount);
						}
						callCloseActionAsync(url);
						removeNotificationItem();
					});
				}
			};

			this.getCloseButtonOfItem = function () {
				return $item.find(".close").first();
			}

			/**
			 * Interface returned by this function for public use (see return statement bellow)
			 * The contained functions are implemented bellow
			 */
			var public_object_interace = {
				/**
				 * Used to register the close action on the Item if such an action is given.
				 * Note that not all items are closable. Close action removes the item
				 * from the list, and fires a callback to the server to notify the respective
				 * endpoint on the server, that this item has been closed.
				 */
				registerCloseAction: this.registerCloseAction,
				/**
				 * If an item contains aggregate items, they will be shown if the aggregating item
				 * is clicked. This interaction is registered on the item here.
				 */
				registerAggregates: this.registerAggregates,
				/**
				 * Checks if there is any Novelty Counter inside the counter the given object
				 */
				replaceByAsyncItem: this.replaceByAsyncItem,
				/**
				 * Checks if there is any Status Counter inside the counter the given object
				 */
				replaceContentByAsyncItemContent: this.replaceContentByAsyncItemContent,

				addAsyncAggregate: this.addAsyncAggregate,
				/**
				 * Checks if there is any Status Counter inside the counter the given object
				 */
				getCloseButtonOfItem: this.getCloseButtonOfItem,


				getCounterObjectIfAny: this.getCounterObjectIfAny
			};

			var copyContent = function($to,$from, parts){
				parts.forEach(function (part) {
					console.log(part);
					console.log($from.find(part).html());

					$to.find(part).first().html($from.find(part).html());
				});;
			}


			var performAsyncCall = function(url,send_data,callback){
				$.ajax({
					url: url,
					data: send_data,
					type: "POST"
				}).done(function(data) {
					callback(data);
				});
			}



			/**
			 * Firing to callback to the endpoint on the server
			 *
			 * @private
			 * @param $item
			 * @param $aggregates
			 */
			var callCloseActionAsync = function(url){
				$.ajax({
					url: url
				}).done(function(data) {
					$item.append(data);
				});
			};

			/**
			 * Removing the closed item from the list.
			 *
			 * @private
			 * @param $close_button
			 */
			var removeNotificationItem = function () {
				console.log($item.siblings().children().length);
				if(!$item.siblings().children(".il-notification-item").length){
					getParentSlateOfItem().hide();
					if($item.parents(".il-aggregate-notifications").length) {
						getParentSlateOfItem().show().siblings().show();
					}
				}
				$item.children().remove();
			};



			/**
			 * Showing aggregates if aggregating item is clicked.
			 *
			 * @private
			 * @param $aggregates
			 */
			var engageAggregatesOfItem = function($aggregates){

				var $parent_slate = getParentSlateOfItem();

				if($parent_slate.length){
					$parent_slate.siblings().hide();
					$parent_slate.hide();
					$aggregates.insertAfter($parent_slate).show();
				}else{
					$aggregates.insertAfter($item).show();
					$item.hide();
				}
			};


			/**
			 * Hiding aggregates, if the user navigates back to the top level.
			 *
			 * @private
			 * @param $item
			 * @param $aggregates
			 */
			var disEngageAggregatesOfItem = function($aggregates){
				var $parent_slate = getParentSlateOfItem();
				if($parent_slate.length){
					$parent_slate.siblings().show();
					$parent_slate.show();
				}
				$item.show().append($aggregates);
				$aggregates.hide();
			};

			var getNotificationsTriggererIfAny = function(){
				var $meta_bar = getMetaBarOfItemIfIsInOne();
				if($meta_bar.length){
					var $notification_glyph = $meta_bar.find('.il-metabar-entries > .btn-bulky .glyphicon-bell');
					return $notification_glyph.parents('.btn-bulky');
				}
			}

			/**
			 * Gets and returns the Meta Bar if there is one
			 */
			var getMetaBarOfItemIfIsInOne = function(){
				return $item.parents('.il-maincontrols-metabar');
			}

			var getAggregatesOfItem = function(){
				$parent = getParentSlateOfItem().parent();
				if(!$parent.length){
					$parent = $('body');
				}
				return $parent.find(".il-aggregate-notifications[data-aggregatedby="+getId()+"]");
			}

			/**
			 * Get the slate, that contains the item given
			 *
			 * @private
			 * @param $item
			 */
			var getParentSlateOfItem = function(){
				return $item.parents(".il-maincontrols-slate-notification");
			};

			var getId = function(){
				console.log("get id: "+$item.children().attr('id'));
				return $item.find(".il-notification-item").first().attr('id');
			}

			return public_object_interace;
		};

		return public_interface;
	})($);
})($, il.UI.item);