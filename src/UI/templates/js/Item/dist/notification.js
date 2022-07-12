(function () {
	'use strict';

	var notificationItemFactory = function($, counterFactory) {
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

			//Make sure *this* in notificationItemObject is properly bound.
			var NotificationItemConstructor = notificationItemObject.bind({});
			return NotificationItemConstructor($item, $, counterFactory);
		};

		/**
		 * Interface returned by this function for public use
		 */
		var public_interface = {
			getNotificationItemObject: getNotificationItemObject,
		};

		return public_interface;
	};

	/**
	 * Declaration and implementation of the Notification Item object.
	 * Those functions are available through the object provided by getNotificationItemObject
	 */
	var notificationItemObject = function($item, $, counterFactory){
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
		 * @returns {notificationItemObject}
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
		 * @returns {notificationItemObject}
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
		 * @returns {notificationItemObject}
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
				return counterFactory($).getCounterObject(getNotificationsTriggererIfAny());
			}
		};

		/**
		 * Used to register the aggregates section and the necessary actioins.
		 * All Notification Items have such a section, however, if this section is empty it is not accessible.
		 *
		 * Note this is usually only used internally or by the Notification Item renderer.
		 *
		 * @public
		 * @param bool prevent_toggle
		 * @returns {notificationItemObject}
		 */
		this.registerAggregates = function(prevent_toggle){
			var $aggregates = getAggregatesOfItem().hide();

			$aggregates.find(".il-maincontrols-slate-notification-title").click(function(){
				disEngageAggregatesOfItem($aggregates);
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
		 * @returns {notificationItemObject}
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
		 * @returns {notificationItemObject}
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
		 * Used to set the description of a notification item, note the description field must be rendered to be set.
		 * @param {string} text
		 * @returns {notificationItemObject}
		 */
		this.setItemDescription = function(text) {
			var $description = $item.find(".il-item-description");
			if($description.length == 0){
				throw "No Description Field in DOM for given Notification Item";
			}
			$description.text(text);
			return this;
		};

		/**
		 * Used to get the description of a notification item
		 * @returns {string}
		 */
		this.getItemDescription = function() {
			return $item.find(".il-item-description").text();
		};

		/**
		 * Used to remove all properties of a notification item.
		 * @returns {notificationItemObject}
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
		 * @returns {notificationItemObject}
		 */
		this.setItemPropertyValueAtPosition = function(text, position) {
			getPropertyValueField(position).text(text);
			return this;
		};

		/**
		 * Used to set the value for the n-th property
		 * @param {number} position
		 * @returns {string}
		 */
		this.getItemPropertyValueAtPosition = function(position) {
			return getPropertyValueField(position).text();
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
		};

		/**
		 * Checks if an item has any siblings
		 * @returns {boolean}
		 */
		this.hasSibblings = function () {
			return this.getNrOfSibblings() > 0;
		};

		/**
		 * Get Number of Sibblings
		 * @returns {boolean}
		 */
		this.getNrOfSibblings = function () {
			return $item.siblings().children(".il-notification-item").length;
		};

		/**
		 * return the parent item or false, if the item is not an aggregate
		 * @returns {this}
		 */
		this.getParentItem = function(){
			if(!this.isAggregate()) {
				return false;
			}

			return notificationItemFactory($, counterFactory)
				.getNotificationItemObject($item.parents(".il-item-notification-replacement-container"));
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
			getItemDescription: this.getItemDescription,
			removeItemProperties: this.removeItemProperties,
			setItemPropertyValueAtPosition: this.setItemPropertyValueAtPosition,
			getItemPropertyValueAtPosition: this.getItemPropertyValueAtPosition,
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
		 * Used to set the value for the n-th property
		 * @param {number} position
		 * @returns $property_field
		 */
		var getPropertyValueField = function(position) {
			let $item_property_values = $item.find(".il-item-properties .il-item-property-value");

			if($item_property_values.length == 0){
				throw "No properties exist for in DOM for given Notification Item"
			}else if($item_property_values.length < position){
				throw "No property with position "+position+" doest not exist for given Notification Item"
			}
			return $item_property_values.eq(position - 1);
		};

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
		};

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
			});	};

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
			}else {
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
		 * @param $close_button
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
			var $parent = getParentSlateOfItem().parent();
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
		};

		/**
		 * Gets and returns the Meta Bar if there is one
		 *
		 * @returns jQuery Object of Meta Bar
		 */
		var getMetaBarOfItemIfIsInOne = function(){
			return $item.parents('.il-maincontrols-metabar');
		};

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
		};

		return public_object_interface;
	};

	var counterFactory = function($){
	    /**
	     * Name of the counter class in the DOM
	     * @private
	     */
	    var _cls_counter = 'il-counter';

	    /**
	     * See Interface description
	     */
	    var getCounterObject = function($object_containing_counter){
	        let $counter;
	        $counter = getCounterJquery($object_containing_counter);
	        console.assert($counter.length > 0, "Passed jQuery Object does not contain a counter");
	        return bindCounterJquery($counter);
	    };

	    /**
	     * See Interface description
	     */
	    var getCounterObjectOrNull = function($object_containing_counter){
	        let $counter;
	        $counter = getCounterJquery($object_containing_counter);
	        if($counter.length === 0){
	            return null;
	        }
	        return bindCounterJquery($counter);
	    };

	    /**
	     * Interface returned by this function for public use (see return statement bellow)
	     * The contained functions are implemented bellow
	     */
	    var public_interface = {
	        /**
	         * Function grasping the $ version of the counter in the DOM,
	         * an generating the public interface of the counter object.
	         *
	         * Note that the return object might contain more than one
	         * counter representation in the DOM. Mutators and queries
	         * will be applied to all the contained representations.
	         */
	        getCounterObject: getCounterObject,

	        /**
	         * Same as getCounterObject but allows returning null if
	         * no counter object is present.
	         */
	        getCounterObjectOrNull: getCounterObjectOrNull
	    };

	    /**
	     * get the Jquery Object of the counte
	     * @param $object_containing_counter
	     */
	    var getCounterJquery = function($object_containing_counter){
	        console.assert($object_containing_counter instanceof $,
	            "$object_containing_counter is not a jQuery Object, param: "+$object_containing_counter);

	        var $counter = $object_containing_counter;
	        if(!$object_containing_counter.hasClass(_cls_counter)){
	            $counter = $object_containing_counter.find("."+_cls_counter);
	        }
	        return $counter;
	    };

	    /**
	     * Make sure *this* in generateCounterObject is properly bound.
	     * @param $counter
	     */
	    var bindCounterJquery = function($counter){
	        var CounterObjectConstructor = counterObject.bind({});
	        return CounterObjectConstructor($counter,$);
	    };

	    return public_interface;
	};

	/**
	 * Declaration and implementation of the counter object
	 */
	var counterObject = function($counter,$){
	    /**
	     * Storing jQuery instance of the DOM of the Counter
	     * in the current scope for internal access.
	     */
	    var $counter = $counter;

	    /**
	     * Name of the novelty class in the DOM
	     * @private
	     */
	    var _cls_counter_novelty = 'il-counter-novelty';

	    /**
	     * Name of the counter class in the DOM
	     * @private
	     */
	    var _cls_counter_status = 'il-counter-status';

	    /**
	     * Constants used to explain failed assertions
	     */
	    const MISSING_COUNTERTYPE_EXCEPTION_MSG = " Counter does not exist in the DOM. " +
	        "Make sure the respective Counter type has been rendered before applying this operations.";
	    const NOT_A_NUMBER_MSG = " is not a number";

	    /**
	     * Note, since the following functions will be offered on the public
	     * interface and the returned scope will be bound to a jQuery instance,
	     * we have to bind the functions to this, to set the scope right here.
	     */

	    /**
	     * See Interface description
	     */
	    this.getStatusCount = function() {
	        return getCount(getStatusObject($counter))
	    };

	    /**
	     * See Interface description
	     */
	    this.getNoveltyCount = function() {
	        return getCount(getNoveltyObject($counter))
	    };

	    /**
	     * See Interface description
	     */
	    this.hasNoveltyObject = function(){
	        return getNoveltyObject($counter).length>0;
	    };

	    /**
	     * See Interface description
	     */
	    this.hasStatusObject = function(){
	        return getStatusObject($counter).length>0;
	    };

	    /**
	     * See Interface description
	     */
	    this.setNoveltyTo = function(value) {
	        console.assert(this.hasNoveltyObject(),"Novelty "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(typeof value == 'number',value+NOT_A_NUMBER_MSG);

	        var $novelty = getNoveltyObject($counter);

	        $novelty.html(value);
	        if(value === 0){
	            $novelty.hide();
	        }else {
	            $novelty.show();
	        }
	        return this;
	    };

	    /**
	     * See Interface description
	     */
	    this.setStatusTo = function(value) {
	        console.assert(this.hasStatusObject(),"Status "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(typeof value == 'number',value+NOT_A_NUMBER_MSG);

	        var $status = getStatusObject($counter);

	        $status.html(value);
	        if(value === 0){
	            $status.hide();
	        }else {
	            $status.show();
	        }
	        return this;
	    };

	    /**
	     * See Interface description
	     */
	    this.incrementNoveltyCount = function(value) {
	        console.assert(this.hasNoveltyObject(),"Novelty "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(typeof value == 'number',value+NOT_A_NUMBER_MSG);

	        performWorkOnEachSingleCounter(function(single_counter){
	            if(single_counter.hasNoveltyObject()){
	                single_counter.setNoveltyTo(single_counter.getNoveltyCount()+value);
	            }
	        },$counter);
	        return this;
	    };

	    /**
	     * See Interface description
	     */
	    this.decrementNoveltyCount = function(value) {
	        console.assert(this.hasNoveltyObject(),"Novelty "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(typeof value == 'number',value+NOT_A_NUMBER_MSG);

	        performWorkOnEachSingleCounter(function(single_counter){
	            if(single_counter.hasNoveltyObject()) {
	                single_counter.setNoveltyTo(single_counter.getNoveltyCount() - value);
	            }
	        },$counter);
	        return this;
	    };

	    /**
	     * See Interface description
	     */
	    this.incrementStatusCount = function(value) {
	        console.assert(this.hasStatusObject(),"Status "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(typeof value == 'number',value+NOT_A_NUMBER_MSG);

	        performWorkOnEachSingleCounter(function(single_counter){
	            if(single_counter.hasStatusObject()) {
	                single_counter.setStatusTo(single_counter.getStatusCount() + value);
	            }
	        },$counter);
	        return this;
	    };

	    /**
	     * See Interface description
	     */
	    this.decrementStatusCount = function(value) {
	        console.assert(this.hasStatusObject(),"Status "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(typeof value == 'number',value+NOT_A_NUMBER_MSG);

	        performWorkOnEachSingleCounter(function(single_counter){
	            if(single_counter.hasStatusObject()) {
	                single_counter.setStatusTo(single_counter.getStatusCount() - value);
	            }
	        },$counter);
	        return this;
	    };

	    /**
	     * See Interface description
	     */
	    this.setTotalNoveltyToStatusCount = function() {
	        console.assert(this.hasStatusObject(),"Status "+MISSING_COUNTERTYPE_EXCEPTION_MSG);
	        console.assert(this.hasNoveltyObject(),"Novelty "+MISSING_COUNTERTYPE_EXCEPTION_MSG);

	        return this.incrementStatusCount(this.getNoveltyCount()).setNoveltyTo(0);
	    };

	    /**
	     * Interface returned by this function for public use (see return statement bellow)
	     * The contained functions are implemented bellow
	     */
	    var public_object_interace = {
	        /**
	         * Gets sum of the count of all Novelty Counter inside the given counter object
	         */
	        getNoveltyCount: this.getNoveltyCount,
	        /**
	         * Gets sum of count of all Status Counter inside the given counter object
	         */
	        getStatusCount: this.getStatusCount,
	        /**
	         * Checks if there is any Novelty Counter inside the counter the given object
	         */
	        hasNoveltyObject: this.hasNoveltyObject,
	        /**
	         * Checks if there is any Status Counter inside the counter the given object
	         */
	        hasStatusObject: this.hasStatusObject,
	        /**
	         * Sets all Novelty Counters inside a given counter object to a set value
	         */
	        setNoveltyTo: this.setNoveltyTo,
	        /**
	         * Sets all Status Counters inside a given counter object to a set value
	         */
	        setStatusTo: this.setStatusTo,
	        /**
	         * Increments all Novelty Counters inside a counter Object by some given value
	         */
	        incrementNoveltyCount: this.incrementNoveltyCount,
	        /**
	         * Decrements all Novelty Counters inside a counter Object by some given value
	         */
	        decrementNoveltyCount: this.decrementNoveltyCount,
	        /**
	         * Increments all Status Counters inside a counter Object by some given value
	         */
	        incrementStatusCount: this.incrementStatusCount,
	        /**
	         * Decrements all Status Counters inside a counter Object by some given value
	         */
	        decrementStatusCount: this.decrementStatusCount,
	        /**
	         * Collects to total sum of all Novelity Counters and increments all Status Counters
	         * by this value
	         */
	        setTotalNoveltyToStatusCount: this.setTotalNoveltyToStatusCount
	    };

	    /**
	     * Gets the novelty part of the counter object
	     * @private
	     */
	    var getNoveltyObject = function($parent){
	        return $parent.find("."+_cls_counter_novelty);
	    };

	    /**
	     * Gets the status part of the counter object
	     *
	     * @private
	     */
	    var getStatusObject = function($parent){
	        return $parent.find("."+_cls_counter_status);
	    };

	    /**
	     * Gets the count of either the status or the novelty part of the counter object.
	     *
	     * @private
	     */
	    var getCount = function($novelty_or_status_object){
	        var sum = 0;
	        $novelty_or_status_object.each(function() {
	            //Note that this in this anonymous function points to the
	            //object in $novelty_or_status_object currently looped
	            var count = $(this).text();
	            sum += parseInt(count);
	        });

	        return sum;
	    };

	    /**
	     * Helper used for syntactically sugering looping over jQuery elements in the
	     * jQuery representation of the counter. Needed if an operations (workload)
	     * needs to be applied to each element individually.
	     *
	     * @param workload
	     */
	    var performWorkOnEachSingleCounter = function(workload,$parent){
	        //Note that we have to change each DOM counter instance individually.
	        //Therefore we generate a separate counter instance
	        $parent.each(function() {
	            //Note that *this* in this anonymous function points to the
	            //object in counter jquery object currently looped
	            var single_counter = counterFactory($).getCounterObject($(this));
	            workload(single_counter,$(this));
	        });
	    };

	    return public_object_interace;
	};

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
	il.UI.item.notification = notificationItemFactory($,counterFactory);

}());
