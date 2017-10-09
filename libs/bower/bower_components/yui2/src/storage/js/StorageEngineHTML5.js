/*
 * HTML limitations:
 *  - 5MB in FF and Safari, 10MB in IE 8
 *  - only FF 3.5 recovers session storage after a browser crash
 *
 * Thoughts:
 *  - how can we not use cookies to handle session
 */
(function() {
	// internal shorthand
var Util = YAHOO.util,
	Lang = YAHOO.lang,

	/*
	 * Required for IE 8 to make synchronous.
	 */
	_beginTransaction = function(driver) {
		driver.begin();
	},

	/*
	 * Required for IE 8 to make synchronous.
	 */
	_commitTransaction = function(driver) {
		driver.commit();
	},

	/**
	 * The StorageEngineHTML5 class implements the HTML5 storage engine.
	 * @namespace YAHOO.util
	 * @class StorageEngineHTML5
	 * @constructor
	 * @extend YAHOO.util.Storage
	 * @param sLocation {String} Required. The storage location.
	 * @param oConf {Object} Required. A configuration object.
	 */
	StorageEngineHTML5 = function(sLocation, oConf) {
		var that = this,
            oDriver = window[sLocation];
        
		StorageEngineHTML5.superclass.constructor.call(that, sLocation, StorageEngineHTML5.ENGINE_NAME, oConf);// not set, are cookies available

		// simplifieds the begin/commit functions, if not using IE; this provides a massive performance boost
		if (! oDriver.begin) {_beginTransaction = function() {}; }
		if (! oDriver.commit) {_commitTransaction = function() {}; }

		that.length = oDriver.length;
        that._driver = oDriver;
        that.fireEvent(Util.Storage.CE_READY);
	};

	Lang.extend(StorageEngineHTML5, Util.Storage, {

		_driver: null,

		/*
		 * Implementation to clear the values from the storage engine.
		 * @see YAHOO.util.Storage._clear
		 */
		_clear: function() {
			var that = this, i, sKey;

			if (that._driver.clear) {
				that._driver.clear();
			}
			// for FF 3, fixed in FF 3.5
			else {
				for (i = that.length; 0 <= i; i -= 1) {
					sKey = that._key(i);
					that._removeItem(sKey);
				}
			}
		},

		/*
		 * Implementation to fetch an item from the storage engine.
		 * @see YAHOO.util.Storage._getItem
		 */
		_getItem: function(sKey) {
			var o = this._driver.getItem(sKey);
			return Lang.isObject(o) ? o.value : o; // for FF 3, fixed in FF 3.5
		},

		/*
		 * Implementation to fetch a key from the storage engine.
		 * @see YAHOO.util.Storage._key
		 */
		_key: function(nIndex) {return this._driver.key(nIndex);},

		/*
		 * Implementation to remove an item from the storage engine.
		 * @see YAHOO.util.Storage._removeItem
		 */
		_removeItem: function(sKey) {
			var oDriver = this._driver;
			_beginTransaction(oDriver);
			oDriver.removeItem(sKey);
			_commitTransaction(oDriver);
			this.length = oDriver.length;
		},

		/*
		 * Implementation to remove an item from the storage engine.
		 * @see YAHOO.util.Storage._setItem
		 */
		_setItem: function(sKey, oValue) {
			var oDriver = this._driver;

			try {
				_beginTransaction(oDriver);
				oDriver.setItem(sKey, oValue);
				_commitTransaction(oDriver);
				this.length = oDriver.length;
				return true;
			}
			catch (e) {
				return false;
			}
		}
	}, true);

	StorageEngineHTML5.ENGINE_NAME = 'html5';
    
	StorageEngineHTML5.isAvailable = function() {
        try {
            return ('localStorage' in window) && window['localStorage'] !== null &&
                    ('sessionStorage' in window) && window['sessionStorage'] !== null;
        }
        catch (e) {
            /*
                In FireFox and maybe other browsers, you can disable storage in the configuration settings, which
                will cause an error to be thrown instead of evaluating the simple if/else statement.
             */
            return false;
        }
    };

    Util.StorageManager.register(StorageEngineHTML5);
	Util.StorageEngineHTML5 = StorageEngineHTML5;
}());