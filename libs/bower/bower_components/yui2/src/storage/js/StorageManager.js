/**
 * The StorageManager class is a singleton that registers DataStorage objects and returns instances of those objects.
 * @class StorageManager
 * @namespace YAHOO.util
 * @static
 */
(function() {
	// internal shorthand
var Util = YAHOO.util,
	Lang = YAHOO.lang,

	// private variables
	_locationEngineMap = {}, // cached engines
	_registeredEngineSet = [], // set of available engines
	_registeredEngineMap = {}, // map of available engines
	
	/**
	 * Fetches a storage constructor if it is available, otherwise returns NULL.
	 * @method _getClass
	 * @param fnClass {Function} Required. The storage constructor to test.
	 * @return {Function} An available storage constructor or NULL.
	 * @private
	 */
	_getClass = function(fnClass) {
		return (fnClass && fnClass.isAvailable()) ? fnClass : null;
	},

	/**
	 * Fetches the storage engine from the cache, or creates and caches it.
	 * @method _getStorageEngine
	 * @param sLocation {String} Required. The location to store.
	 * @param fnClass {Function} Required. A pointer to the engineType Class.
	 * @param oConf {Object} Optional. Additional configuration for the data source engine.
	 * @private
	 */
	_getStorageEngine = function(sLocation, fnClass, oConf) {
		var engine = _locationEngineMap[sLocation + fnClass.ENGINE_NAME];

		if (! engine) {
			engine = new fnClass(sLocation, oConf);
			_locationEngineMap[sLocation + fnClass.ENGINE_NAME] = engine;
		}

		return engine;
	},

	/**
	 * Ensures that the location is valid before returning it or a default value.
	 * @method _getValidLocation
	 * @param sLocation {String} Required. The location to evaluate.
	 * @private
	 */
	_getValidLocation = function(sLocation) {
		switch (sLocation) {
			case Util.StorageManager.LOCATION_LOCAL:
			case Util.StorageManager.LOCATION_SESSION:
				return sLocation;

			default: return Util.StorageManager.LOCATION_SESSION;
		}
	};

	// public namespace
	Util.StorageManager = {

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
		 * @param sLocation {String} Optional. The storage location - LOCATION_SESSION & LOCATION_LOCAL; default is LOCAL.
		 * @param oConf {Object} Optional. Additional configuration for the getting the storage engine.
		 * {
		 * 	engine: {Object} configuration parameters for the desired engine
		 * 	force: {Boolean} force the <code>engineType</code> or fail
		 * 	order: {Array} an array of storage engine names; the desired order to try engines}
		 * }
		 * @static
		 */
		get: function(engineType, sLocation, oConf) {
			var oCfg = Lang.isObject(oConf) ? oConf : {},
				fnClass = _getClass(_registeredEngineMap[engineType]),
                i , j;

			if (! fnClass && ! oCfg.force) {
				if (oCfg.order) {
					j = oCfg.order.length;

					for (i = 0; i < j && ! fnClass; i += 1) {
						fnClass = _getClass(oCfg.order[i]);
					}
				}

				if (! fnClass) {
					j = _registeredEngineSet.length;

					for (i = 0; i < j && ! fnClass; i += 1) {
						fnClass = _getClass(_registeredEngineSet[i]);
					}
				}
			}

			if (fnClass) {
				return _getStorageEngine(_getValidLocation(sLocation), fnClass, oCfg.engine);
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
		 * @param engineConstructor {Function} Required. The engine constructor function, see engines.
		 * @return {Boolean} When successfully registered.
		 * @static
		 */
		register: function(engineConstructor) {
			if (Lang.isFunction(engineConstructor) && Lang.isFunction(engineConstructor.isAvailable) &&
                    Lang.isString(engineConstructor.ENGINE_NAME)) {
				_registeredEngineMap[engineConstructor.ENGINE_NAME] = engineConstructor;
				_registeredEngineSet.push(engineConstructor);
				return true;
			}

			return false;
		}
	};

	YAHOO.register("StorageManager", Util.SWFStore, {version: "@VERSION@", build: "@BUILD@"});
}());