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
		 * See Interface description
		 * @param id
		 * @param url
		 */
		var registerCloseAction = function(id,url) {
			var $close_button = $('#'+id);
			console.log(id);

			$close_button.click(function(){
				removeNotificationItem($close_button);
				callCloseActionAsync(url);
			});
		};

		/**
		 * See Interface description
		 * @param id
		 */
		var registerAggregatesToggle = function(id){
			var $item = $('#'+id);
			var $title = $item.find(".il-item-notification-title");
			var $aggregates = $("div[data-aggregatedby="+id+"]").hide();

			$title.find("a").attr("href", "#");
			$title.click(function(event){
				engageAggregatesOfItem($item,$aggregates);
			});

			$aggregates.find(".il-maincontrols-slate-notification-title").click(function(){
				disEngageAggregatesOfItem($item,$aggregates)
			});
		};

		/**
		 * Note that the Name "Public Interface" here indicates, that those two functions will be
		 * used outside of this scope. However, the only consumer currently is the "withAdditionalOnLoadCode"
		 * function in the renderer of the items.
		 *
		 * @type {{registerCloseAction: registerCloseAction, registerAggregatesToggle: registerAggregatesToggle}}
		 */
		var public_interface =  {
			/**
			 * Used to register the close action on the Item if such an action is given.
			 * Note that not all items are closable. Close action removes the item
			 * from the list, and fires a callback to the server to notify the respective
			 * endpoint on the server, that this item has been closed.
			 *
			 */
			registerCloseAction: registerCloseAction,
			/**
			 * If an item contains aggregate items, they will be shown if the aggregating item
			 * is clicked. This interaction is registered on the item here.
			 */
			registerAggregatesToggle: registerAggregatesToggle
		}

		/**
		 * Showing aggregates if aggregating item is clicked.
		 *
		 * @private
		 * @param $item
		 * @param $aggregates
		 */
		var engageAggregatesOfItem = function($item,$aggregates){
			$item.hide();

			var $parent_slate = getParentSlateOfItem($item)

			if($parent_slate.length){
				$parent_slate.siblings().hide();
				$parent_slate.hide();
				$aggregates.insertAfter($parent_slate).show();
			}else{
				$aggregates.insertAfter($item).show();
			}
		};

		/**
		 * Hiding aggregates, if the user navigates back to the top level.
		 *
		 * @private
		 * @param $item
		 * @param $aggregates
		 */
		var disEngageAggregatesOfItem = function($item,$aggregates){
			$item.show();
			var $parent_slate = getParentSlateOfItem($item);
			if($parent_slate.length){
				$parent_slate.siblings().show();
				$parent_slate.show();
			}
			$item.show().append($aggregates);
			$aggregates.hide();
		};

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
				//Nothing to be done here.
			});
		};

		/**
		 * Removing the closed item from the list.
		 *
		 * @private
		 * @param $item
		 * @param $aggregates
		 */
		var removeNotificationItem = function ($close_button) {
			var $item = $close_button.parents(".il-notification-item");
			var $item_siblings = $item.siblings(".il-notification-item");
			if(!$item_siblings.length){
				getParentSlateOfItem($item).remove();
			}
			$item.remove();
		};

		/**
		 * Get the slate, that contains the item given
		 * 
		 * @private
		 * @param $item
		 * @param $aggregates
		 */
		var getParentSlateOfItem = function($item){
			return $item.parents(".il-maincontrols-slate-notification");
		};

		return public_interface;
	})($);
})($, il.UI.item);