/*
 * Copyright (c) 2009, Matt Snider, LLC. All rights reserved.
 * Version: 0.2.00
 */

/*
 * HTML limitations:
 *  - only 100,000 bytes of data can be stored this way
 *
 * Thoughts:
 *  - how can we not use cookies to handle session
 */
(function() {
		// internal shorthand
    var Y = YAHOO.util;

	/**
	 * The StorageEngineHTML5 class implements the HTML5 storage engine.
	 * @namespace YAHOO.util
	 * @class StorageEngineHTML5
	 * @constructor
	 * @extend YAHOO.util.Storage
	 * @param location {String} Required. The storage location.
	 * @param conf {Object} Required. A configuration object.
	 */
	Y.StorageEngineHTML5 = function(location, conf) {
		Y.StorageEngineHTML5.superclass.constructor.call(this, location, Y.StorageEngineHTML5.ENGINE_NAME, conf);// not set, are cookies available
		this._engine = window[location];
		this.length = this._engine.length;
		this.fireEvent(this.CE_READY);
	};

	YAHOO.lang.extend(Y.StorageEngineHTML5, Y.Storage, {

		/*
		 * Implementation to clear the values from the storage engine.
		 * @see YAHOO.util.Storage._clear
		 */
		_clear: function() {this._engine.clear();},

		/*
		 * Implementation to fetch an item from the storage engine.
		 * @see YAHOO.util.Storage._getItem
		 */
		_getItem: function(key) {return this._engine.getItem(key);},

		/*
		 * Implementation to fetch a key from the storage engine.
		 * @see YAHOO.util.Storage._key
		 */
		_key: function(index) {return this._engine.key(index);},

		/*
		 * Implementation to remove an item from the storage engine.
		 * @see YAHOO.util.Storage._removeItem
		 */
		_removeItem: function(key) {
			this._engine.removeItem(key);
			this.length = this._engine.length;
		},

		/*
		 * Implementation to remove an item from the storage engine.
		 * @see YAHOO.util.Storage._setItem
		 */
		_setItem: function(key, value) {
			this._engine.setItem(key, this._createValue(value));
			this.length = this._engine.length;
		}
	}, true);

	Y.StorageEngineHTML5.ENGINE_NAME = 'html5';
    Y.StorageManager.register(Y.StorageEngineHTML5.ENGINE_NAME, function() {return window.localStorage;}, Y.StorageEngineHTML5);
}());