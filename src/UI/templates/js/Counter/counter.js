var il = il || {};
il.UI = il.UI || {};

/**
 * Declaration and implementation of the il.UI.counter scope.
 *
 * Note that this scope provides a public interface through which counters can
 * be accessed and manipulated by the client side.
 *
 * This scope contains only the getCounterObject through which a counter object can
 * be accessed.
 *
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
		this.getCounterObject = function($object_containing_counter){
			let $counter;
			$counter = getCounterJquery($object_containing_counter);
			console.assert($counter.length > 0, "Passed jQuery Object does not contain a counter");
			return bindCounterJquery($counter);
		};

		/**
		 * See Interface description
		 */
		this.getCounterObjectOrNull = function($object_containing_counter){
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
			getCounterObject: this.getCounterObject,

			/**
			 * Same as getCounterObject but allows returning null if
			 * no counter object is present.
			 */
			getCounterObjectOrNull: this.getCounterObjectOrNull
		};

		/**
		 * get the Jquery Object of the counte
		 * @param $object_containing_counter
		 */
		var getCounterJquery = function($object_containing_counter){
			console.assert($object_containing_counter instanceof jQuery,
				"$object_containing_counter is not a jQuery Object, param: "+$object_containing_counter);

			var $counter = $object_containing_counter;
			if(!$object_containing_counter.hasClass(_cls_counter)){
				$counter = $object_containing_counter.find("."+_cls_counter);
			}
			return $counter;
		}

		/**
		 * Make sure *this* in generateCounterObject is properly bound.
		 * @param $counter
		 */
		var bindCounterJquery = function($counter){
			var CounterObjectConstructor = generateCounterObject.bind({});
			return CounterObjectConstructor($counter);
		}

		/**
		 * Declaration and implementation of the counter object
		 */
		var generateCounterObject = function($counter){
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
				}else{
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
				}else{
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
					var single_counter = getCounterObject($(this));
					workload(single_counter,$(this));
				});
			};

			return public_object_interace;
		};

		return public_interface;
	})($);
})($, il.UI);
