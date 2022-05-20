il = il || {};
il.UI = il.UI || {};
il.UI.item = il.UI.item || {};

/**
 * Scope for JS code for the Notification Items in the UI Components.
 *
 * Note that this scope provides a public interface through which Notification Items can
 * be accessed and manipulated by the client side. Note that this is the same pattern as is used by
 * counter.js
 *
 * This scope contains only the getNotificationItemObject through which a Notification Item object can
 * be accessed.
 *
 * See the public_object_interface below for a list of functions of this object offered
 * to the public. Also see the extended asyc Main Controls Meta Bar example for a detailed
 * show case of the provided functionality.
 *
 * Example Usage:
 *
 * //Step 1: Get the Notification Item Object
 * var il.MyScoope.myNotificationItem = il.UI.item.notification.getNotificationItemObject($('selector'));
 *
 * Note that it is probably best to grap the selector directly from the item itself like so:
 *
 * $async_item = $item->withAdditionalOnLoadCode(function($id) {
 *   return "il.MyScoope.myNotificationItem  = il.UI.item.notification.getNotificationItemObject($($id));";
 * });
 *
 * //Step 2: Do stuff with the Notification Item Object
 * il.MyScoope.myNotificationItem.replaceByAsyncItem('some_url',{some_data});
 *
 * //Step 3: Note that you can also get the counter if the object is placed in the Meta Bar like so:
 * il.MyScoope.myNotificationItem.getCounterObjectIfAny().incrementNoveltyCount(10);
 */
(function($, item ) {
	item.notification = (function($) {
		/**
		 * Name of the outermost Notification Item class in the DOM. This is
		 * where our internal $item will point to. Even if the complete
		 * Notification Item is replaced, this will remain in the DOM to give
		 * the object a valid access for further actions (e.g. putting something new
		 * in there), or modifying counters.
		 *
		 * @private
		 */
		var _cls_item_container = 'il-item-notification-replacement-container';


		/**

		 /**
		 * The argument passed mussed be the jQuery Object of some element residing inside
		 * a Notification Item Object. Then, the function searches the jQuery Notification Slate
		 * object in the DOM and creates an new Notification Slate object by using the
		 * generateNotificationSlateObject function
		 *
		 * @public
		 */
		var getNotificationItemObject= function($item_or_object_inside_item){
			console.assert($item_or_object_inside_item instanceof jQuery,
				"$item_or_object_inside_item is not a jQuery Object, param: "+$item_or_object_inside_item);

			var $item = $item_or_object_inside_item;
			if(!$item.hasClass(_cls_item_container)){
				$item = $item_or_object_inside_item.closest("."+_cls_item_container);
			}
			console.assert($item.length > 0, "Passed jQuery Object does not contain a Notification Item");

			//Make sure *this* in generateNotificationItemObject is properly bound.
			var NotificationItemConstructor = generateNotificationItemObject.bind({});
			return NotificationItemConstructor($item);
		};

		/**
		 * Interface returned by this function for public use
		 */
		var public_interface = {
			getNotificationItemObject: getNotificationItemObject,
		};



		/**
		 * Declaration and implementation of the Notification Item object.
		 * Those functions are available through the object provided by getNotificationItemObject
		 */
		var generateNotificationItemObject = function($item){
			/**
			 * jQuery object pointing to the outmost il-item-notification-replacement-container
			 * div.
			 */
			var $item = $item;

			/**
			 * Replaces the complete Notification Item along with its
			 * aggregates. Note that $item remains valid, since
			 * it points to an outer container.
			 *
			 * @public
			 * @param url
			 * @param send_data
			 * @returns {generateNotificationItemObject}
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

			/**
			 * Replaces only the data of the Notification Item
			 * not it's aggregates. This can be used, if
			 * e.g. only a time property or description text has to be
			 * changed, and not the whole list of aggregates.
			 *
			 * @public
			 * @param url
			 * @param send_data
			 * @returns {generateNotificationItemObject}
			 */
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

			/**
			 * Adds an additional aggregate to the Notification Item returned
			 * by the URL called async.
			 *
			 * @public
			 * @param url
			 * @param send_data
			 * @returns {generateNotificationItemObject}
			 */
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

			/**
			 * Returns the Object, if the context the Items resides in provides such an Object.
			 * Note, that one has to manipulate counters manually, if the async methods are used.
			 *
			 * @public
			 * @returns {generateCounterObject}
			 */
			this.getCounterObjectIfAny = function(){
				var $meta_bar = getMetaBarOfItemIfIsInOne();
				if($meta_bar.length){
					return il.UI.counter.getCounterObject(getNotificationsTriggererIfAny());
				}
			}

			/**
			 * Used to register the aggregates section and the necessary actioins.
			 * All Notification Items have such a section, however, if this section is empty it is not accessible.
			 *
			 * Note this is usually only used internally or by the Notification Item renderer.
			 *
			 * @public
			 * @param bool prevent_toggle
			 * @returns {generateNotificationItemObject}
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
				return this;
			};

			/**
			 * Used to register the close action on the Item if such an action is given.
			 * Note that not all items are closable. Close action removes the item
			 * from the list, and fires a callback to the server to notify the respective
			 * endpoint on the server, that this item has been closed.
			 *
			 * Note this is usually only used internally or by the Notification Item renderer.
			 * Others just provide an URL on the Notification Item Component and work from there.
			 *
			 * Note that JS logic might be returned by the server, which would be
			 * attached to the DOM and executed if properly wrapped. See the extended
			 * Meta Bar example.
			 *
			 * @public
			 * @param string url
			 * @param int amount
			 * @returns {generateNotificationItemObject}
			 */
			this.registerCloseAction = function(url, amount) {
				var self = this;
				var $close_button = this.getCloseButtonOfItem();
				if($close_button.length && url !== '#'){
					$close_button.click(function(){
						//Do not decrement if we deal with an aggregate that still has sibblings.
						if(!self.isAggregate() || ! self.hasSibblings()){
							var $counter = self.getCounterObjectIfAny();
							if($counter){
								$counter.decrementNoveltyCount(amount);
							}
						}

						performAsyncCall(url,{},function(data) {
							$item.append(data);
						});
						removeNotificationItem(self);
					});
				}
				return this;
			};

			/**
			 * Used to remove a notification item.
			 * In contrast to registerCloseAction this could be used by a consuming
			 * service to remove a known item from the UI.
			 *
			 * @public
			 * @param decrementCounterValue
			 * @returns {generateNotificationItemObject}
			 */
			this.closeItem = function(decrementCounterValue = 0) {
				let self = this,
					$counter = self.getCounterObjectIfAny();

				if ($counter && decrementCounterValue > 0) {
					$counter.decrementNoveltyCount(decrementCounterValue);
				}

				removeNotificationItem(self);
				return this;
			};

			/**
			 * Used to close the notification center completely.
			 * Calling this method has the same effect like manually clicking
			 * on the triggerer notification bell.
			 * @returns {jQuery|!jQuery}
			 */
			this.closeNotificationCenter = function () {
				let $meta_bar = getMetaBarOfItemIfIsInOne();
				if ($meta_bar.length) {
					getNotificationsTriggererIfAny()
					.filter(".engaged")
					.trigger("click");
				}
			};

			/**
			 * Used to set the description of a notification item
			 * @param {string} text
			 * @returns {generateNotificationItemObject}
			 */
			this.setItemDescription = function(text) {
				$item.find(".il-item-description").text(text);

				return this;
			};

			/**
			 * Used to remove all properties of a notification item.
			 * @returns {generateNotificationItemObject}
			 */
			this.removeItemProperties = function() {
				$item.find(".il-item-divider").remove();
				$item.find(".il-item-properties").remove();

				return this;
			};

			/**
			 * Used to set the value for the n-th property
			 * @param {string} text
			 * @param {number} position
			 * @returns {generateNotificationItemObject}
			 */
			this.setItemPropertyValueAtPosition = function(text, position) {
				$item.find(".il-item-properties .il-item-property-value").eq(position - 1).text(text);

				return this;
			};

			/**
			 * Return a handle to the close Button, in case
			 * additional magic needs to be placed on this button.
			 *
			 * @public
			 * @returns jQuery Close Button
			 */
			this.getCloseButtonOfItem = function () {
				return $item.find(".close").first();
			}

			/**
			 * Checks if an item has any siblings
			 * @returns {boolean}
			 */
			this.hasSibblings = function () {
				return this.getNrOfSibblings() > 0;
			}

			/**
			 * Get Number of Sibblings
			 * @returns {boolean}
			 */
			this.getNrOfSibblings = function () {
				return $item.siblings().children(".il-notification-item").length;
			}

			/**
			 * return the parent item or false, if the item is not an aggregate
			 * @returns {this}
			 */
			this.getParentItem = function(){
				if(!this.isAggregate()) {
					return false;
				}

				return getNotificationItemObject($item.parents("."+_cls_item_container));
			};

			/**
			 * Checks if an item is an aggregate, aggregated by some other item
			 * @returns {boolean}
			 */
			this.isAggregate = function(){
				return $item.parents(".il-aggregate-notifications").length > 0;
			};

			/**
			 * Interface returned by this function for public use
			 * The contained functions are implemented below
			 */
			var public_object_interface = {
				closeNotificationCenter: this.closeNotificationCenter,
				setItemDescription: this.setItemDescription,
				removeItemProperties: this.removeItemProperties,
				setItemPropertyValueAtPosition: this.setItemPropertyValueAtPosition,
				closeItem: this.closeItem,
				registerCloseAction: this.registerCloseAction,
				registerAggregates: this.registerAggregates,
				replaceByAsyncItem: this.replaceByAsyncItem,
				replaceContentByAsyncItemContent: this.replaceContentByAsyncItemContent,
				addAsyncAggregate: this.addAsyncAggregate,
				getCloseButtonOfItem: this.getCloseButtonOfItem,
				getCounterObjectIfAny: this.getCounterObjectIfAny,
				hasSibblings: this.hasSibblings,
				getNrOfSibblings: this.getNrOfSibblings,
				getParentItem: this.getParentItem,
				isAggregate: this.isAggregate
			};


			/**
			 * The following function are all internal.
			 */

			/**
			 * Just some syntactic sugar for the ajax call.
			 * Note that we send data per GET, due to semantical
			 * correctness, see discussion in:
			 * https://github.com/ILIAS-eLearning/ILIAS/pull/2329
			 *
			 * @private
			 * @param url
			 * @param send_data
			 * @param callback
			 */
			var performAsyncCall = function(url,send_data,callback){
				$.ajax({
					url: url,
					data: send_data,
					type: "GET"
				}).done(function(data) {
					callback(data);
				});
			}


			/**
			 * Copies a set of divs to another. Used
			 * to exchange the content of an old to a new
			 * version of the notification item.
			 *
			 * @private
			 * @param $to
			 * @param $from
			 * @param parts
			 */
			var copyContent = function($to,$from, parts){
				parts.forEach(function (part) {
					$to.find(part).first().html($from.find(part).html());
				});;
			}

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
				$aggregates.find(':focusable').first().focus();
			};


			/**
			 * Hiding aggregates, if the user navigates back to the top level.
			 *
			 * @private
			 * @param $item
			 */
			var disEngageAggregatesOfItem = function($aggregates){
				var $parent_slate = getParentSlateOfItem();
				if($parent_slate.length){
					$parent_slate.siblings().show();
					$parent_slate.show();
					$parent_slate.find(':focusable').first().focus();
				}
				$item.show().append($aggregates);
				$aggregates.hide();
			};
			/**
			 * Removes an Notificaiton Item and the aggretas.
			 * Note that depending on the state after removing, some
			 * additional cleaning up needs to be done.
			 *
			 * @private
			 * @param self
			 */
			var removeNotificationItem = function (self) {
				if(!self.hasSibblings()){
					getParentSlateOfItem().hide();
					if(self.isAggregate()) {
						getParentSlateOfItem().show().siblings().show();
					}
				}
				$item.children().remove();
			};

			/**
			 * Get the jQuery Object of the Aggregates of the Item
			 * @returns jQuery Object of the Aggregates of the Item
			 */
			var getAggregatesOfItem = function(){
				$parent = getParentSlateOfItem().parent();
				if(!$parent.length){
					$parent = $('body');
				}
				return $parent.find(".il-aggregate-notifications[data-aggregatedby="+getId()+"]");
			};

			/**
			 * Get the slate, that contains the item given
			 * @returns {*}
			 */
			var getParentSlateOfItem = function(){
				return $item.parents(".il-maincontrols-slate-notification");
			};

			/**
			 * Returns the Id of the Notification Item from the DOM
			 * @returns sting Id
			 */
			var getId = function(){
				return $item.find(".il-notification-item").first().attr('id');
			}

			/**
			 * Gets and returns the Meta Bar if there is one
			 *
			 * @returns jQuery Object of Meta Bar
			 */
			var getMetaBarOfItemIfIsInOne = function(){
				return $item.parents('.il-maincontrols-metabar');
			}

			/**
			 * Gets the jQuery Object of the triggerer of the Notifications
			 * if any.
			 *
			 * Personal Note: This is not placed on the very bottom by accident.
			 * This is the furthest level of doom to be found here and I am not proud
			 * of it. Hopefully this will never be found. It is a shame and needs to be
			 * get rid of in the next revision (see also UI Components Roadmap). This
			 * access to the triggerer feels like waking in the midst of a highway with blindfolds
			 * on during rush hour.
			 *
			 * @returns jQuery Object of the triggerer of the Notifications
			 */
			var getNotificationsTriggererIfAny = function(){
				var $meta_bar = getMetaBarOfItemIfIsInOne();
				if($meta_bar.length){
					var $notification_glyph = $meta_bar.find('li > .btn-bulky .glyphicon-bell');
					return $notification_glyph.parents('.btn-bulky');
				}
			}

			return public_object_interface;
		};

		return public_interface;
	})($);
})($, il.UI.item);