/*
 * Copyright (c) 2009, Matt Snider, LLC. All rights reserved.
 * Version: 0.2.00
 */
(function() {
var Y = YAHOO.util,
	YL = YAHOO.lang;

	/**
	 * The StorageEngineKeyed class implements the interface necessary for managing keys.
	 * @namespace YAHOO.util
	 * @class StorageEngineKeyed
	 * @constructor
	 * @extend YAHOO.util.Storage
	 */
	Y.StorageEngineKeyed = function() {
		Y.StorageEngineKeyed.superclass.constructor.apply(this, arguments);
		this._keys = [];
	};

	YL.extend(Y.StorageEngineKeyed, Y.Storage, {

		/**
		 * The a collectinon of key applicable to the current location. This should never be edited by the developer.
		 * @property _keys
		 * @type {Array}
		 * @protected
		 */
		_keys: null,

		/**
		 * Evaluates if a key exists in the keys array; indexOf does not work in all flavors of IE.
		 * @method _indexOfKey
		 * @param key {String} Required. The key to evaluate.
		 * @protected
		 */
		_indexOfKey: function(key) {
			if (this._keys) {
				Y.StorageEngineKeyed.prototype._indexOfKey = [].indexOf ? function(key) {
					return this._keys.indexOf(key);
				}: function(key) {
					for (var i = this._keys.length - 1; 0 <= i; i -= 1) {
						if (key === this._keys[i]) {return i;}
					}

					return -1;
				};

				return this._indexOfKey(key);
			}
		},

		/**
		 * Removes a key from the keys array.
		 * @method _removeKey
		 * @param key {String} Required. The key to remove.
		 * @protected
		 */
		_removeKey: function(key) {
			if (this._keys) {
				var j = this._indexOfKey(key),
					rest = this._keys.slice(j + 1);

				this._keys.length = j;
				this._keys.concat(rest);
				this.length = this._keys.length;
			}
		}
	});
}());