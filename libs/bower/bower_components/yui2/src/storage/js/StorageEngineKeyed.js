(function() {
var Util = YAHOO.util;

	/**
	 * The StorageEngineKeyed class implements the interface necessary for managing keys.
	 * @namespace YAHOO.util
	 * @class StorageEngineKeyed
	 * @constructor
	 * @extend YAHOO.util.Storage
	 */
	Util.StorageEngineKeyed = function() {
		Util.StorageEngineKeyed.superclass.constructor.apply(this, arguments);
		this._keys = [];
		this._keyMap = {};
	};

	YAHOO.lang.extend(Util.StorageEngineKeyed, Util.Storage, {

		/**
		 * A collection of keys applicable to the current location. This should never be edited by the developer.
		 * @property _keys
		 * @type {Array}
		 * @protected
		 */
		_keys: null,

		/**
		 * A map of keys to their applicable position in keys array. This should never be edited by the developer.
		 * @property _keyMap
		 * @type {Object}
		 * @protected
		 */
		_keyMap: null,

		/**
		 * Adds the key to the set.
		 * @method _addKey
		 * @param sKey {String} Required. The key to evaluate.
		 * @protected
		 */
		_addKey: function(sKey) {
		    if (!this._keyMap.hasOwnProperty(sKey)) {
    			this._keys.push(sKey);
			    this._keyMap[sKey] = this.length;
			    this.length = this._keys.length;
			}
		},

		/*
		 * Implementation to clear the values from the storage engine.
		 * @see YAHOO.util.Storage._clear
		 */
		_clear: function() {
			this._keys = [];
			this.length = 0;
		},

		/**
		 * Evaluates if a key exists in the keys array; indexOf does not work in all flavors of IE.
		 * @method _indexOfKey
		 * @param sKey {String} Required. The key to evaluate.
		 * @protected
		 */
		_indexOfKey: function(sKey) {
			var i = this._keyMap[sKey];
			return undefined === i ? -1 : i;
		},

		/*
		 * Implementation to fetch a key from the storage engine.
		 * @see YAHOO.util.Storage.key
		 */
		_key: function(nIndex) {return this._keys[nIndex];},

		/**
		 * Removes a key from the keys array.
		 * @method _removeItem
		 * @param sKey {String} Required. The key to remove.
		 * @protected
		 */
		_removeItem: function(sKey) {
			var that = this,
                j = that._indexOfKey(sKey),
				rest = that._keys.slice(j + 1),
                k;

			delete that._keyMap[sKey];

			// update values in keymap that are greater than current position
			for (k in that._keyMap) {
				if (j < that._keyMap[k]) {
					that._keyMap[k] -= 1;
				}
			}
			
			that._keys.length = j;
			that._keys = that._keys.concat(rest);
			that.length = that._keys.length;
		}
	});
}());