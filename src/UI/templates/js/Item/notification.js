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
				closeNotificationItem($close_button);
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

		var public_interface =  {
			/**
			 * Used to register the close action on the Item if such an action is given
			 */
			registerCloseAction: registerCloseAction,
			/**
			 * Used to register the toggling interaction, if such an interaction is given
			 */
			registerAggregatesToggle: registerAggregatesToggle
		}

		/**
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
		 * @private
		 * @param $item
		 * @param $aggregates
		 */
		var closeNotificationItem = function ($close_button) {
			var $item = $close_button.parents(".il-notification-item");
			var $item_siblings = $item.siblings(".il-notification-item");
			if(!$item_siblings.length){
				getParentSlateOfItem($item).remove();
			}
			$item.remove();
		};

		/**
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