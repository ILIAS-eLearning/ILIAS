(function() {
var Y = YAHOO.util,
	YD = Y.Dom,
	YE = Y.Event,

	_currentKeys = YD.get('currentKeys'),
	_enginePicker = YD.get('enginePicker'),
	_link = null,
	_location = Y.StorageManager.LOCATION_LOCAL,
	_YS = null;

/**
 * Inserts a key into the key list.
 * @method _addItem
 * @param key {String} Required. The key.
 * @private
 */
var _addItem = function(key) {
	var node = _currentKeys.appendChild(document.createElement('a'));
	node.href = '';
	node.appendChild(document.createTextNode(key));
};

var _updateEngine = function() {
	var engineType = _enginePicker.options[_enginePicker.selectedIndex].value;
	if (_YS) {_YS.unsubscribeAll(_YS.CE_CHANGE);}

	switch (engineType) {
		case Y.StorageEngineCookie.ENGINE_NAME:
			_YS = Y.StorageManager.get(engineType, _location);
		break;
		case Y.StorageEngineGears.ENGINE_NAME:
			_YS = Y.StorageManager.get(engineType, _location);
		break;
		case Y.StorageEngineSWF.ENGINE_NAME:
			_YS = Y.StorageManager.get(engineType, _location);
		break;
		case Y.StorageEngineHTML5.ENGINE_NAME:
			_YS = Y.StorageManager.get(engineType, _location);
		break;
		default:
	}

	var fx = function() {
		_YS.subscribe(_YS.CE_CHANGE, function(e) {
			var isSetItem = null !== e.newValue,
				isNewItem = null === e.oldValue;

			if (isSetItem) {
				if (isNewItem) {
					_addItem(e.key);
				}
			}
			else {
				if (_link) {
					var key = _link.innerHTML;
					_link.parentNode.removeChild(_link);
					_link = null;
					_YS.removeItem(key);
				}
			}

			YD[_currentKeys.childNodes.length ? 'removeClass' : 'addClass']('btnDelete', 'disabled');
		});

		_currentKeys.innerHTML = '';

		for (var i = 0; i < _YS.length; i += 1) {
			_addItem(_YS.key(i));
		}
	};

	// note: this will cause double subscription with SWF; need to figure out a better pattern
	_YS.__yui_events[_YS.CE_READY].subscribeEvent.unsubscribeAll();
	_YS.__yui_events[_YS.CE_READY].subscribeEvent.subscribe(fx);
	_YS.subscribe(_YS.CE_READY, fx);
};

// change the engine
YE.on(_enginePicker, 'change', _updateEngine);

// save or update a storage key
YE.on('btnSave', 'click', function() {
	var key = YD.get('fieldKey').value,
		data = YD.get('fieldData').value;

	YD.get('fieldData').value = '';
	YD.get('fieldKey').value = '';

	if (key && data) {
		_YS.setItem(key, data);
	}
});

// alert the length of storage
YE.on('btnLength', 'click', function() {
	alert( _YS.length);
});

// delete currently selected storage key
YE.on('btnDelete', 'click', function(e) {
	var targ = YE.getTarget(e);

	if (! YD.hasClass(targ, 'disabled')) {
		_YS.removeItem(YD.get('fieldKey').value);
		YD.get('fieldData').value = '';
		YD.get('fieldKey').value = '';
	}
});

// clear storage data
YE.on('btnClear', 'click', function(e) {
	YD.get('fieldData').value = '';
	YD.get('fieldKey').value = '';
	_currentKeys.innerHTML = '';
	_YS.clear();
});

YE.on('localCheckbox', 'change', function(e) {
	_location = Y.StorageManager[YE.getTarget(e).checked ? 'LOCATION_SESSION' : 'LOCATION_LOCAL'];
	_updateEngine();
});

YE.on(_currentKeys, 'click', function(e) {
	var targ = YE.getTarget(e);

	if (targ && 'a' === ('' + targ.tagName).toLowerCase()) {
		YE.preventDefault(e);
		_link = targ;
		var key = _link.innerHTML;
		YD.get('fieldKey').value = key;
		YD.get('fieldData').value = _YS.getItem(key);
		YD[_currentKeys.childNodes.length ? 'removeClass' : 'addClass']('btnDelete', 'disabled');
	}
});

_updateEngine();
}());