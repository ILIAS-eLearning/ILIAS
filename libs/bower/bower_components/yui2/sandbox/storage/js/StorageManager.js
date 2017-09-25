/*
 * Copyright (c) 2009, Matt Snider, LLC. All rights reserved.
 * Version: 0.2.00
 */

// todo: move the session ID method to this

/**
 * The Storage module manages client-side data storage.
 * @module Storage
 */

/**
 * The StorageManager class is a singleton that registers DataStorage objects and returns instances of those objects.
 * @class StorageManager
 * @namespace YAHOO.util
 * @static
 */
(function() {
	// internal shorthand
var Y = YAHOO.util,
	YL = YAHOO.lang,

	// private variables
	_locationEngineMap = {}, // cached engines
	_registeredEngineSet = [], // set of available engines
	_registeredEngineMap = {}, // map of available engines

	/**
	 * Fetches the storage engine from the cache, or creates and caches it.
	 * @method _getStorageEngine
	 * @param location {String} Required. The location to store.
	 * @param klass {Function} Required. A pointer to the engineType Class.
	 * @param conf {Object} Optional. Additional configuration for the data source engine.
	 * @private
	 */
	_getStorageEngine = function(location, klass, conf) {
		var engine = _locationEngineMap[location + klass.ENGINE_NAME];

		if (! engine) {
			engine = new klass(location, conf);
			_locationEngineMap[location + klass.ENGINE_NAME] = engine;
		}

		return engine;
	},

	/**
	 * Ensures that the location is valid before returning it or a default value.
	 * @method _getValidLocation
	 * @param location {String} Required. The location to evaluate.
	 * @private
	 */
	_getValidLocation = function(location) {
		switch (location) {
			case Y.StorageManager.LOCATION_LOCAL:
			case Y.StorageManager.LOCATION_SESSION:
				return location;
			break;

			default:
				return Y.StorageManager.LOCATION_SESSION;
			break;
		}
	};

	// public namespace
	Y.StorageManager = {

        /**
         * The storage location - session; data cleared at the end of a user's session.
         * @property LOCATION_SESSION
         * @type {String}
         * @static
         */
		LOCATION_SESSION: 'sessionStorage',

        /**
         * The storage location - local; data cleared on demand.
         * @property LOCATION_LOCAL
         * @type {String}
         * @static
         */
		LOCATION_LOCAL: 'localStorage',

		/**
		 * Fetches the desired engine type or first available engine type.
		 * @method get
		 * @param engineType {String} Optional. The engine type, see engines.
		 * @param location {String} Optional. The storage location - LOCATION_SESSION & LOCATION_LOCAL; default is LOCAL.
		 * @param conf {Object} Optional. Additional configuration for the getting the storage engine.
		 * {
		 * 	engine: {Object} configuration parameters for the desired engine
		 * 	order: {Array} an array of storage engine names; the desired order to try engines}
		 * }
		 * @static
		 */
		get: function(engineType, location, conf) {
			var _cfg = YL.isObject(conf) ? conf : {},
				klass = _registeredEngineMap[engineType];

			if (! klass) {
				var i, j;

				if (_cfg.order) {
					j = _cfg.order.length;

					for (i = 0; i < j && ! klass; i += 1) {
						klass = _registeredEngineMap[_cfg.order[i]];
					}
				}

				if (! klass) {
					j = _registeredEngineSet.length;

					for (i = 0; i < j && ! klass; i += 1) {
						klass = _registeredEngineSet[i];
					}
				}
			}

			if (klass) {
				return _getStorageEngine(_getValidLocation(location), klass, _cfg.engine);
			}

			throw('YAHOO.util.StorageManager.get - No engine available, please include an engine before calling this function.');
		},

        /*
         * Estimates the size of the string using 1 byte for each alpha-numeric character and 3 for each non-alpha-numeric character.
         * @method getByteSize
         * @param s {String} Required. The string to evaulate.
         * @return {Number} The estimated string size.
         * @private
         */
        getByteSize: function(s) {
			return encodeURIComponent('' + s).length;
        },

		/**
		 * Registers a engineType Class with the StorageManager singleton; first in is the first out.
		 * @method register
		 * @param engineType {String} Required. The engine type, see engines.
		 * @param validationFx {Function} Required. The evaluation function to test if engineType is available.
		 * @param klass {Function} Required. A pointer to the engineType Class.
		 * @return {Boolean} When successfully registered.
		 * @static
		 */
		register: function(engineType, validationFx, klass) {
			if (YL.isString(engineType) && YL.isFunction(validationFx) && YL.isFunction(klass) && validationFx()) {
				_registeredEngineMap[engineType] = klass;
				_registeredEngineSet.push(klass);
				return true;
			}

			return false;
		}
	};
}());