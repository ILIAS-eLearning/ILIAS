(function() {

/**
 * The StorageEvent class manages the storage events by emulating the HTML 5 implementation.
 * @namespace YAHOO.util
 * @class StorageEvent
 * @constructor
 * @param oStorageArea {Object} Required. The Storage object that was affected.
 * @param sKey {String} Required. The key being changed; DOMString in HTML 5 spec.
 * @param oOldValue {Mixed} Required. The old value of the key being changed; DOMString in HTML 5 spec.
 * @param oNewValue {Mixed} Required. The new value of the key being changed; DOMString in HTML 5 spec.
 * @param sType {String} Required. The storage event type.
 */
function StorageEvent(oStorageArea, sKey, oOldValue, oNewValue, sType) {
	this.key = sKey;
	this.oldValue = oOldValue;
	this.newValue = oNewValue;
	this.url = window.location.href;
	this.window = window; // todo: think about the CAJA and innocent code
	this.storageArea = oStorageArea;
	this.type = sType;
}

YAHOO.lang.augmentObject(StorageEvent, {
	TYPE_ADD_ITEM: 'addItem',
	TYPE_REMOVE_ITEM: 'removeItem',
	TYPE_UPDATE_ITEM: 'updateItem'
});

StorageEvent.prototype = {

    /**
     * The 'key' attribute represents the key being changed.
     * @property key
     * @type {String}
     * @static
     * @readonly
     */
    key: null,

    /**
     * The 'newValue' attribute represents the new value of the key being changed.
     * @property newValue
     * @type {Mixed}
     * @static
     * @readonly
     */
    newValue: null,

    /**
     * The 'oldValue' attribute represents the old value of the key being changed.
     * @property oldValue
     * @type {Mixed}
     * @static
     * @readonly
     */
    oldValue: null,

    /**
     * The 'source' attribute represents the WindowProxy object of the browsing context of the document whose key changed.
     * @property source
     * @type {Object}
     * @static
     * @readonly
     */
    source: null,

    /**
     * The 'storageArea' attribute represents the Storage object that was affected.
     * @property storageArea
     * @type {Object}
     * @static
     * @readonly
     */
    storageArea: null,

    /**
     * The 'type' attribute represents the Storage event type.
     * @property type
     * @type {Object}
     * @static
     * @readonly
     */
    type: null,

    /**
     * The 'url' attribute represents the address of the document whose key changed.
     * @property url
     * @type {String}
     * @static
     * @readonly
     */
    url: null
};

YAHOO.util.StorageEvent = StorageEvent;
	
}());