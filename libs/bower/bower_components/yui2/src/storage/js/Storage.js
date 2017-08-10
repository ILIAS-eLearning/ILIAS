/**
 * The Storage module manages client-side data storage.
 * @module Storage
 */

(function() {

	// internal shorthand
var Y = YAHOO,
	Util = Y.util,
	Lang = Y.lang,
	_logOverwriteError,
    Storage,

	RX_TYPE = /^type=(\w+)/i,
	RX_VALUE = /&value=(.*)/i;

if (! Util.Storage) {
	_logOverwriteError = function(fxName) {
		Y.log('Exception in YAHOO.util.Storage.?? - must be extended by a storage engine'.replace('??', fxName).replace('??', this.getName ? this.getName() : 'Unknown'), 'error');
	};

	/**
	 * The Storage class is an HTML 5 storage API clone, used to wrap individual storage implementations with a common API.
	 * @class Storage
	 * @namespace YAHOO.util
	 * @constructor
	 * @param sLocation {String} Required. The storage location.
	 * @parm sName {String} Required. The engine name.
	 * @param oConf {Object} Required. A configuration object.
	 */
	Storage = function(sLocation, sName, oConf) {
		var that = this;
		Y.env._id_counter += 1;

		// protected variables
		that._cfg = Lang.isObject(oConf) ? oConf : {};
		that._location = sLocation;
		that._name = sName;
		that.isReady = false;

		// public variables
		that.createEvent(Storage.CE_READY, {scope: that, fireOnce: true});
		that.createEvent(Storage.CE_CHANGE, {scope: that});
		
		that.subscribe(Storage.CE_READY, function() {
			that.isReady = true;
		});
	};

    Storage.CE_READY = 'YUIStorageReady';
    Storage.CE_CHANGE = 'YUIStorageChange';

	Storage.prototype = {

		/**
		 * The event name for when the storage item is ready.
		 * @property CE_READY
		 * @type {String}
		 * @public
		 */
		CE_READY: Storage.CE_READY,

		/**
		 * The event name for when the storage item has changed.
		 * @property CE_CHANGE
		 * @type {String}
		 * @public
		 */
		CE_CHANGE: Storage.CE_CHANGE,

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
		 * This engine singleton has been initialized already.
		 * @property isReady
		 * @type {String}
		 * @protected
		 */
		isReady: false,

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
		 * @param sKey {String} Required. The key used to reference this value (DOMString in HTML 5 spec).
		 * @return {String|NULL} The value stored at the provided key (DOMString in HTML 5 spec).
		 * @public
		 */
		getItem: function(sKey) {
			Y.log("Fetching item at  " + sKey);
			var oItem = this._getItem(sKey);
			return Lang.isValue(oItem) ? this._getValue(oItem) : null; // required by HTML 5 spec
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
		 * @param sKey {String} Required. The key to search for.
		 * @return {Boolean} True when key has been set.
		 * @public
		 */
		hasKey: function(sKey) {
			return Lang.isString(sKey) && this._hasKey(sKey);
		},

		/**
		 * Retrieve the key stored at the provided index; should be overwritten by storage engine.
		 * @method key
		 * @param nIndex {Number} Required. The index to retrieve (unsigned long in HTML 5 spec).
		 * @return {String} Required. The key at the provided index (DOMString in HTML 5 spec).
		 * @public
		 */
		key: function(nIndex) {
			Y.log("Fetching key at " + nIndex);

			if (Lang.isNumber(nIndex) && -1 < nIndex && this.length > nIndex) {
				var value = this._key(nIndex);
				if (value) {return value;}
			}

			// this is thrown according to the HTML5 spec
			throw('INDEX_SIZE_ERR - Storage.setItem - The provided index (' + nIndex + ') is not available');
		},

		/**
		 * Remove an item from the data storage.
		 * @method removeItem
		 * @param sKey {String} Required. The key to remove (DOMString in HTML 5 spec).
		 * @public
		 */
		removeItem: function(sKey) {
			Y.log("removing " + sKey);
            var that = this,
                oOldValue;
			
			if (that.hasKey(sKey)) {
                oOldValue = that._getItem(sKey);
                if (! oOldValue) {oOldValue = null;}
                that._removeItem(sKey);
				that.fireEvent(Storage.CE_CHANGE, new Util.StorageEvent(that, sKey, oOldValue, null, Util.StorageEvent.TYPE_REMOVE_ITEM));
			}
			else {
				// HTML 5 spec says to do nothing
			}
		},

		/**
		 * Adds an item to the data storage.
		 * @method setItem
		 * @param sKey {String} Required. The key used to reference this value (DOMString in HTML 5 spec).
		 * @param oData {Object} Required. The data to store at key (DOMString in HTML 5 spec).
		 * @public
		 * @throws QUOTA_EXCEEDED_ERROR
		 */
		setItem: function(sKey, oData) {
			Y.log("SETTING " + oData + " to " + sKey);
			
			if (Lang.isString(sKey)) {
				var that = this,
                    oOldValue = that._getItem(sKey);

				if (! oOldValue) {oOldValue = null;}

				if (that._setItem(sKey, that._createValue(oData))) {
					that.fireEvent(Storage.CE_CHANGE, new Util.StorageEvent(that, sKey, oOldValue, oData,
                            that.hasKey(sKey) ? Util.StorageEvent.TYPE_UPDATE_ITEM : Util.StorageEvent.TYPE_ADD_ITEM));
				}
				else {
					// that is thrown according to the HTML5 spec
					throw('QUOTA_EXCEEDED_ERROR - Storage.setItem - The choosen storage method (' +
						  that.getName() + ') has exceeded capacity');
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
		 * @param o {Object} Required. An object to store.
		 * @protected
		 */
		_createValue: function(o) {
			var sType = (Lang.isNull(o) || Lang.isUndefined(o)) ? ('' + o) : typeof o;
			return 'type=' + sType + '&value=' + encodeURIComponent('' + o);
		},

		/**
		 * Implementation of the getItem login; should be overwritten by storage engine.
		 * @method _getItem
		 * @param sKey {String} Required. The key used to reference this value.
		 * @return {String|NULL} The value stored at the provided key.
		 * @protected
		 */
		_getItem: function(sKey) {
			_logOverwriteError('_getItem');
			return '';
		},

		/**
		 * Converts the stored value into its appropriate type.
		 * @method _getValue
		 * @param s {String} Required. The stored value.
		 * @protected
		 */
		_getValue: function(s) {
			var sType = s.match(RX_TYPE)[1],
				sValue = s.match(RX_VALUE)[1];

			switch (sType) {
				case 'boolean': return 'true' == sValue;
				case 'number': return parseFloat(sValue);
				case 'null': return null;
				default: return decodeURIComponent(sValue);
			}
		},

		/**
		 * Implementation of the key logic; should be overwritten by storage engine.
		 * @method _key
		 * @param nIndex {Number} Required. The index to retrieve (unsigned long in HTML 5 spec).
		 * @return {String|NULL} Required. The key at the provided index (DOMString in HTML 5 spec).
		 * @protected
		 */
		_key: function(nIndex) {
			_logOverwriteError('_key');
			return '';
		},

		/*
		 * Implementation to fetch evaluate the existence of a key.
		 */
		_hasKey: function(sKey) {
			return null !== this._getItem(sKey);
		},

		/**
		 * Implementation of the removeItem login; should be overwritten by storage engine.
		 * @method _removeItem
		 * @param sKey {String} Required. The key to remove.
		 * @protected
		 */
		_removeItem: function(sKey) {
			_logOverwriteError('_removeItem');
			return '';
		},

		/**
		 * Implementation of the setItem login; should be overwritten by storage engine.
		 * @method _setItem
		 * @param sKey {String} Required. The key used to reference this value.
		 * @param oData {Object} Required. The data to storage at key.
		 * @return {Boolean} True when successful, false when size QUOTA exceeded.
		 * @protected
		 */
		_setItem: function(sKey, oData) {
			_logOverwriteError('_setItem');
			return '';
		}
	};

	Lang.augmentProto(Storage, Util.EventProvider);

    Util.Storage = Storage;
}

}());