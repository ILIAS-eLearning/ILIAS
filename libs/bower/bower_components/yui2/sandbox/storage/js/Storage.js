/*
 * Copyright (c) 2009, Matt Snider, LLC. All rights reserved.
 * Version: 0.2.00
 */

(function() {

	// internal shorthand
var Y = YAHOO.util,
	YL = YAHOO.lang;

if (! Y.Storage) {

	var _logOverwriteError = function(fxName) {
		YAHOO.log(_ERROR_OVERWRITTEN.replace('??', fxName).replace('??', this.getName ? this.getName() : 'Unknown'), 'error');
	};

	/**
	 * The Storage class is an HTML 5 storage API clone, used to wrap individual storage implementations with a common API.
	 * @class Storage
	 * @namespace YAHOO.util
	 * @constructor
	 * @param location {String} Required. The storage location.
	 * @parm name {String} Required. The engine name.
	 * @param conf {Object} Required. A configuration object.
	 */
	Y.Storage = function(location, name, conf) {
		YAHOO.env._id_counter += 1;

		// protected variables
		this._cfg = YL.isObject(conf) ? conf : {};
		this._location = location;
		this._name = name;

		// public variables
		this.length = this.length;
		this.createEvent(this.CE_READY, {scope: this});
		this.createEvent(this.CE_CHANGE, {scope: this});
	};

	Y.Storage.prototype = {

		/**
		 * The event name for when the storage item is ready.
		 * @property CE_READY
		 * @type {String}
		 * @public
		 */
		CE_READY: 'YUIStorageReady',

		/**
		 * The event name for when the storage item has changed.
		 * @property CE_CHANGE
		 * @type {String}
		 * @public
		 */
		CE_CHANGE: 'YUIStorageChange',

		/**
		 * The delimiter uesed between the data type and the data.
		 * @property DELIMITER
		 * @type {String}
		 * @public
		 */
		DELIMITER: '__',

		/**
		 * The configuration of the engine.
		 * @property _cfg
		 * @type {Object}
		 * @protected
		 */
		_cfg: '',

		/**
		 * The name of this engine.
		 * @property _name
		 * @type {String}
		 * @protected
		 */
		_name: '',

		/**
		 * The location for this instance.
		 * @property _location
		 * @type {String}
		 * @protected
		 */
		_location: '',

		/**
		 * The current length of the keys.
		 * @property length
		 * @type {Number}
		 * @public
		 */
		length: 0,

		/**
		 * Clears any existing key/value pairs.
		 * @method clear
		 * @public
		 */
		clear: function() {
			this._clear();
			this.length = 0;
		},

		/**
		 * Fetches the data stored and the provided key.
		 * @method getItem
		 * @param key {String} Required. The key used to reference this value (DOMString in HTML 5 spec).
		 * @return {String|NULL} The value stored at the provided key (DOMString in HTML 5 spec).
		 * @public
		 */
		getItem: function(key) {
			YAHOO.log("Fetching item at  " + key);
			var item = this._getItem(key);
			return item ? item : null; // required by HTML 5 spec
		},

		/**
		 * Fetches the storage object's name; should be overwritten by storage engine.
		 * @method getName
		 * @return {String} The name of the data storage object.
		 * @public
		 */
		getName: function() {return this._name;},

		/**
		 * Tests if the key has been set (not in HTML 5 spec); should be overwritten by storage engine.
		 * @method hasKey
		 * @param key {String} Required. The key to search for.
		 * @return {Boolean} True when key has been set.
		 * @public
		 */
		hasKey: function(key) {
			return YL.isString(key) && null !== this.getItem(key);
		},

		/**
		 * Retrieve the key stored at the provided index; should be overwritten by storage engine.
		 * @method key
		 * @param index {Number} Required. The index to retrieve (unsigned long in HTML 5 spec).
		 * @return {String} Required. The key at the provided index (DOMString in HTML 5 spec).
		 * @public
		 */
		key: function(index) {
			YAHOO.log("Fetching key at " + index);

			if (YL.isNumber(index)) {
				var value = this._key(index);
				if (value) {return value;}
			}

			// this is thrown according to the HTML5 spec
			throw('INDEX_SIZE_ERR - Storage.setItem - The provided index (' + index + ') is not available');
		},

		/**
		 * Remove an item from the data storage.
		 * @method setItem
		 * @param key {String} Required. The key to remove (DOMString in HTML 5 spec).
		 * @public
		 */
		removeItem: function(key) {
			YAHOO.log("removing " + key);
			
			if (this.hasKey(key)) {
                var oldValue = this._getItem(key);
                if (! oldValue) {oldValue = null;}
                this._removeItem(key);
				this.fireEvent(this.CE_CHANGE, new Y.StorageEvent(this, key, oldValue, null));
			}
			else {
				// HTML 5 spec says to do nothing
			}
		},

		/**
		 * Adds an item to the data storage.
		 * @method setItem
		 * @param key {String} Required. The key used to reference this value (DOMString in HTML 5 spec).
		 * @param data {Object} Required. The data to store at key (DOMString in HTML 5 spec).
		 * @public
		 * @throws QUOTA_EXCEEDED_ERROR
		 */
		setItem: function(key, data) {
			YAHOO.log("SETTING " + data + " to " + key);
			
			if (YL.isString(key)) {
				var oldValue = this._getItem(key);
				if (! oldValue) {oldValue = null;}

				if (this._setItem(key, data)) {
					this.fireEvent(this.CE_CHANGE, new Y.StorageEvent(this, key, oldValue, data));
				}
				else {
					// this is thrown according to the HTML5 spec
					throw('QUOTA_EXCEEDED_ERROR - Storage.setItem - The choosen storage method (' +
						  this.getName() + ') has exceeded capacity');
				}
			}
			else {
				// HTML 5 spec says to do nothing
			}
		},

		/**
		 * Implementation of the clear login; should be overwritten by storage engine.
		 * @method _clear
		 * @protected
		 */
		_clear: function() {
			_logOverwriteError('_clear');
			return '';
		},

		/**
		 * Converts the object into a string, with meta data (type), so it can be restored later.
		 * @method _createValue
		 * @param s {Object} Required. An object to store.
		 * @protected
		 */
		_createValue: function(s) {
			var type = typeof s;
			return 'string' === type ? s : type + this.DELIMITER + s;
		},

		/**
		 * Implementation of the getItem login; should be overwritten by storage engine.
		 * @method _getItem
		 * @param key {String} Required. The key used to reference this value.
		 * @return {String|NULL} The value stored at the provided key.
		 * @protected
		 */
		_getItem: function(key) {
			_logOverwriteError('_getItem');
			return '';
		},

		/**
		 * Implementation of the key logic; should be overwritten by storage engine.
		 * @method _key
		 * @param index {Number} Required. The index to retrieve (unsigned long in HTML 5 spec).
		 * @return {String|NULL} Required. The key at the provided index (DOMString in HTML 5 spec).
		 * @protected
		 */
		_key: function(index) {
			_logOverwriteError('_key');
			return '';
		},

		/**
		 * Converts the stored value into its appropriate type.
		 * @method _getValue
		 * @param s {String} Required. The stored value.
		 * @protected
		 */
		_getValue: function(s) {
			var a = s ? s.split(this.DELIMITER) : [];
			if (1 == a.length) {return s;}

			switch (a[0]) {
				case 'boolean': return 'true' === a[1];
				case 'number': return parseFloat(a[1]);
				default: return a[1];
			}
		},

		/**
		 * Implementation of the removeItem login; should be overwritten by storage engine.
		 * @method _removeItem
		 * @param key {String} Required. The key to remove.
		 * @protected
		 */
		_removeItem: function(key) {
			_logOverwriteError('_removeItem');
			return '';
		},

		/**
		 * Implementation of the setItem login; should be overwritten by storage engine.
		 * @method _setItem
		 * @param key {String} Required. The key used to reference this value.
		 * @param data {Object} Required. The data to storage at key.
		 * @return {Boolean} True when successful, false when size QUOTA exceeded.
		 * @protected
		 */
		_setItem: function(key, data) {
			_logOverwriteError('_setItem');
			return '';
		}
	};

	YL.augmentProto(Y.Storage, Y.EventProvider);
};

}());