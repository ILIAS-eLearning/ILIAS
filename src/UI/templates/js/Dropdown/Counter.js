var il = il || {};
il.UI = il.UI || {};

/**
 * Declaration and implementation of the il.UI.counter scope.
 *
 * Note that this scope provides a public interface through which counters can
 * be accessed and manipulated by the client side.
 *
 * This scope contains only the getCounterObject through which a counter object can
 * be accessed, which is an extended version of the respective jquery object.
 * See the public_object_interace for a list of functions of this object offered
 * to the public.
 *
 * Example Usage:
 *
 * //Step 1: Get the counter Object
 * var counter = il.UI.counter.getCounterObject($some_jquery_object);
 *
 * //Step 2: Do stuff with the counter Object
 * var novelty_count = counter.setNoveltyCount(3).getNoveltyCount(); //novelty count should be 3
 * novelty_count = counter.setNoveltyToStatus().getNoveltyCount(); //novelty count should be 0, status count 3
 */
(function($, UI) {
	UI.counter = (function() {
		/**
		 * Name of the counter class in the DOM
		 * @private
		 */
		var _cls_counter = 'il-counter';

		/**
		 * See Interface description
		 */
		var getCounterObject = function($object_containing_counter){
			console.assert($object_containing_counter instanceof jQuery,
				"$object_containing_counter is not a jQuery Object, param: "+$object_containing_counter);

			var $counter = $object_containing_counter;
			if(!$object_containing_counter.hasClass(_cls_counter)){
				$counter = $object_containing_counter.find("."+_cls_counter);
			}
			console.assert($counter.length > 0, "Passed jQuery Object does not contain a counter");
			return $.extend($counter,counterObjectExtension());
		};

		/**
		 * Interface returned by this function for public use (see return statement bellow)
		 * The contained functions are implemented bellow
		 */
		var public_interface = {
			/**
			 * Function grasping the $ version of the counter in the DOM, extending it,
			 * with the public interface of the counter object
			 */
			getCounterObject: getCounterObject
		};

		/**
		 * Declaration and implementation of the extension of the $ version of the counter
		 */
		var counterObjectExtension = function(){
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
			 * Note, since the following functions will be offered on the public
			 * interface and the returned scope will be bound to a jQuery instance,
			 * we have to bind the functions to this, to set the scope right here.
			 */

			/**
			 * See Interface description
			 */
			this.getStatusCount = function() {
				return getCount(getStatusObject(this))
			};

			/**
			 * See Interface description
			 */
			this.getNoveltyCount = function() {
				return getCount(getNoveltyObject(this))
			};

			/**
			 * See Interface description
			 */
			this.setNoveltyTo = function(value) {
				var $novelty = getNoveltyObject(this).html(value);

				console.assert($novelty instanceof jQuery,
					"$novelty is not a jQuery Object. Note, the Novelty Counter must " +
					" be rendered by the server to set it's value.");

				console.assert($novelty.length > 0,
					"$novelty is empty. Note, the Novelty Counter  must " +
					" be rendered by the server to set it's value.");

				$novelty.html(value);
				if(value === 0){
					$novelty.hide();
				}else{
					$novelty.show();
				}
				return this;
			};

			/**
			 * See Interface description
			 */
			this.setStatusTo = function(value) {
				var $status = getStatusObject(this);

				console.assert($status instanceof jQuery,
					"$status is not a jQuery Object. Note, the Status Counter must " +
					" be rendered by the server to set it's value.");

				console.assert($status.length > 0,
					"$status is empty. Note, the Status Counter  must " +
					" be rendered by the server to set it's value.");

				$status.html(value);
				if(value === 0){
					$status.hide();
				}else{
					$status.show();
				}
				return this;
			};

			/**
			 * See Interface description
			 */
			this.incrementNoveltyCount = function(value) {
				return this.setNoveltyTo(this.getNoveltyCount()+value);
			};

			/**
			 * See Interface description
			 */
			this.decrementNoveltyCount = function(value) {
				return this.setNoveltyTo(this.getNoveltyCount()-value);
			};

			/**
			 * See Interface description
			 */
			this.incrementStatusCount = function(value) {
				return this.setStatusTo(this.getStatusCount()+value);
			};

			/**
			 * See Interface description
			 */
			this.decrementStatusCount = function(value) {
				return this.setStatusTo(this.getStatusCount()-value);
			};

			/**
			 * See Interface description
			 */
			this.setNoveltyToStatus = function() {
				this.incrementStatusCount(this.getNoveltyCount());
				this.setNoveltyTo(0);
				return this;
			};


			/**
			 * Interface returned by this function for public use (see return statement bellow)
			 * The contained functions are implemented bellow
			 */
			var public_object_interace = {
				getNoveltyCount: this.getNoveltyCount,
				getStatusCount: this.getStatusCount,
				setNoveltyTo: this.setNoveltyTo,
				setStatusTo: this.setStatusTo,
				incrementNoveltyCount: this.incrementNoveltyCount,
				decrementNoveltyCount: this.decrementNoveltyCount,
				incrementStatusCount: this.incrementStatusCount,
				decrementStatusCount: this.decrementStatusCount,
				setNoveltyToStatus: this.setNoveltyToStatus
			};

			/**
			 * Gets the novelty part of the counter object
			 *
			 * Note that this in those function point to the extend version
			 * of the counter object generated in get Counter Object
			 *
			 * @private
			 */
			var getNoveltyObject = function($of_parent){
				return $of_parent.find("."+_cls_counter_novelty);
			};

			/**
			 * Gets the status part of the counter object
			 *
			 * Note that this in those function point to the extend version
			 * of the counter object generated in get Counter Object
			 *
			 * @private
			 */
			var getStatusObject = function($of_parent){
				console.log($of_parent.find("."+_cls_counter_status));
				return $of_parent.find("."+_cls_counter_status);
			};

			/**
			 * Gets the count of either the status or the novelty part of the counter object.
			 *
			 * @private
			 */
			var getCount = function($novelty_or_status_object){
				var sum = 0;
				$novelty_or_status_object.each(function(index) {
					//Note that this in this anonymous function points to the
					//object in $novelty_or_status_object currently looped
					var count = $(this).text();
					sum += parseInt(count);
				});

				return sum;
			};

			return public_object_interace;
		};

		return public_interface;
	})($);
})($, il.UI);