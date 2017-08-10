/*
 * Gears limitation:
 *  - SQLite limitations - http://www.sqlite.org/limits.html
 *  - DB Best Practices - http://code.google.com/apis/gears/gears_faq.html#bestPracticeDB
 * 	- the user must approve before gears can be used
 *  - each SQL query has a limited number of characters (9948 bytes), data will need to be spread across rows
 *  - no query should insert or update more than 9948 bytes of data in a single statement or GEARs will throw:
 *  	[Exception... "'Error: SQL statement is too long.' when calling method: [nsIDOMEventListener::handleEvent]" nsresult: "0x8057001c (NS_ERROR_XPC_JS_THREW_JS_OBJECT)" location: "<unknown>" data: no]
 *
 * Thoughts:
 *  - we may want to implement additional functions for the gears only implementation
 *  - how can we not use cookies to handle session location
 */
(function() {
	// internal shorthand
var Util = YAHOO.util,
	Lang = YAHOO.lang,
	SQL_STMT_LIMIT = 9948,
	TABLE_NAME = 'YUIStorageEngine',

	// local variables
	_driver = null,

	eURI = encodeURIComponent,
	dURI = decodeURIComponent,

	/**
	 * The StorageEngineGears class implements the Google Gears storage engine.
	 * @namespace YAHOO.util
	 * @class StorageEngineGears
	 * @constructor
	 * @extend YAHOO.util.Storage
	 * @param sLocation {String} Required. The storage location.
	 * @param oConf {Object} Required. A configuration object.
	 */
	StorageEngineGears = function(sLocation, oConf) {
		var that = this,
            keyMap = {},
            isSessionStorage, sessionKey, rs;
        
		StorageEngineGears.superclass.constructor.call(that, sLocation, StorageEngineGears.ENGINE_NAME, oConf);

		if (! _driver) {
			// create the database
			_driver = google.gears.factory.create(StorageEngineGears.GEARS);
      // the replace regex fixes http://yuilibrary.com/projects/yui2/ticket/2529411, all ascii characters are allowede except / : * ? " < > | ; ,
			_driver.open(window.location.host.replace(/[\/\:\*\?"\<\>\|;,]/g, '') + '-' + StorageEngineGears.DATABASE);
			_driver.execute('CREATE TABLE IF NOT EXISTS ' + TABLE_NAME + ' (key TEXT, location TEXT, value TEXT)');
		}

		isSessionStorage = Util.StorageManager.LOCATION_SESSION === that._location;
		sessionKey = Util.Cookie.get('sessionKey' + StorageEngineGears.ENGINE_NAME);

		if (! sessionKey) {
			_driver.execute('BEGIN');
			_driver.execute('DELETE FROM ' + TABLE_NAME + ' WHERE location="' + eURI(Util.StorageManager.LOCATION_SESSION) + '"');
			_driver.execute('COMMIT');
		}

		rs = _driver.execute('SELECT key FROM ' + TABLE_NAME + ' WHERE location="' + eURI(that._location) + '"');
		keyMap = {};
	
		try {
			// iterate on the rows and map the keys
			while (rs.isValidRow()) {
				var fld = dURI(rs.field(0));

				if (! keyMap[fld]) {
					keyMap[fld] = true;
					that._addKey(fld);
				}

				rs.next();
			}
		} finally {
			rs.close();
		}

		// this is session storage, ensure that the session key is set
		if (isSessionStorage) {
			Util.Cookie.set('sessionKey' + StorageEngineGears.ENGINE_NAME, true);
		}

        that.fireEvent(Util.Storage.CE_READY);
	};

	Lang.extend(StorageEngineGears, Util.StorageEngineKeyed, {

		/*
		 * Implementation to clear the values from the storage engine.
		 * @see YAHOO.util.Storage._clear
		 */
		_clear: function() {
			StorageEngineGears.superclass._clear.call(this);
			_driver.execute('BEGIN');
			_driver.execute('DELETE FROM ' + TABLE_NAME + ' WHERE location="' + eURI(this._location) + '"');
			_driver.execute('COMMIT');
		},

		/*
		 * Implementation to fetch an item from the storage engine.
		 * @see YAHOO.util.Storage._getItem
		 */
		_getItem: function(sKey) {
			var rs = _driver.execute('SELECT value FROM ' + TABLE_NAME + ' WHERE key="' + eURI(sKey) + '" AND location="' + eURI(this._location) + '"'),
				value = '';

			try {
				while (rs.isValidRow()) {
					value += rs.field(0);
					rs.next();
				}
			} finally {
				rs.close();
			}

			return value ? dURI(value) : null;
		},

		/*
		 * Implementation to remove an item from the storage engine.
		 * @see YAHOO.util.Storage._removeItem
		 */
		_removeItem: function(sKey) {
			YAHOO.log("removing gears key: " + sKey);
			StorageEngineGears.superclass._removeItem.call(this, sKey);
			_driver.execute('BEGIN');
			_driver.execute('DELETE FROM ' + TABLE_NAME + ' WHERE key="' + eURI(sKey) + '" AND location="' + eURI(this._location) + '"');
			_driver.execute('COMMIT');
		},

		/*
		 * Implementation to remove an item from the storage engine.
		 * @see YAHOO.util.Storage._setItem
		 */
		_setItem: function(sKey, oData) {
			YAHOO.log("SETTING " + oData + " to " + sKey);

			this._addKey(sKey);

			var sEscapedKey = eURI(sKey),
				sEscapedLocation = eURI(this._location),
				sEscapedValue = eURI(oData), // escaped twice, maybe not necessary
				aValues = [],
				nLen = SQL_STMT_LIMIT - (sEscapedKey + sEscapedLocation).length,
                i=0, j;

			// the length of the value exceeds the available space
			if (nLen < sEscapedValue.length) {
				for (j = sEscapedValue.length; i < j; i += nLen) {
					aValues.push(sEscapedValue.substr(i, nLen));
				}
			} else {
				aValues.push(sEscapedValue);
			}

			// Google recommends using INSERT instead of update, because it is faster
			_driver.execute('BEGIN');
			_driver.execute('DELETE FROM ' + TABLE_NAME + ' WHERE key="' + sEscapedKey + '" AND location="' + sEscapedLocation + '"');
			for (i = 0, j = aValues.length; i < j; i += 1) {
				_driver.execute('INSERT INTO ' + TABLE_NAME + ' VALUES ("' + sEscapedKey + '", "' + sEscapedLocation + '", "' + aValues[i] + '")');
			}
			_driver.execute('COMMIT');
			
			return true;
		}
	});

	// releases the engine when the page unloads
	Util.Event.on('unload', function() {
		if (_driver) {_driver.close();}
	});

    StorageEngineGears.ENGINE_NAME = 'gears';
	StorageEngineGears.GEARS = 'beta.database';
	StorageEngineGears.DATABASE = 'yui.database';

	StorageEngineGears.isAvailable = function() {
		if (('google' in window) && ('gears' in window.google)) {
			try {
				// this will throw an exception if the user denies gears
				google.gears.factory.create(StorageEngineGears.GEARS);
				return true;
			}
			catch (e) {
				// no need to do anything
			}
		}

		return false;
	};

    Util.StorageManager.register(StorageEngineGears);
	Util.StorageEngineGears = StorageEngineGears;
}());