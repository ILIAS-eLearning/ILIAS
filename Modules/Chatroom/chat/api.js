var _app				= null;
var express				= require('express');
var Namespace			= require('./Model/Namespace');
var Room				= require('./Model/Room');

// Just for extended purpose


/**
 * @returns {Function}
 */
function app() {

	var app = express();

	/**
	 * @type {http.Server|https.Server}
	 * @private
	 */
	var _server = null;

	/**
	 * @type {JSON}
	 * @typedef {Namespace}
	 * @private
	 */
	var _namespaces = {};

	/**
	 *
	 * @type {engine.io/Server}
	 * @private
	 */
	var _io = null;

	/**
	 * @returns {http.Server|https.Server}
	 */
	app.getServer = function() { return _server; };

	/**
	 * @param {string} name
	 * @returns {Namespace}
	 */
	app.getNamespace = function(name) {
		name = name.replace(/^\//, '');
		if(_namespaces[name] !== undefined) {
			return _namespaces[name];
		}
		console.log("Namespace " + name + " does not exists");
	};

	app.getNamespaces = function() {
		return _namespaces;
	};

	/**
	 * @param {http.Server|https.Server} server
	 */
	app.bindServer = function(server) {
		_server = server;
		_io 	= require('socket.io')(server);

		_bindNamespaces();
	};

	/**
	 * Creates a roomId which is used in the chat server.
	 *
	 * @param {number} roomId
	 * @param {number} subRoomId
	 * @returns {string}
	 */
	app.createServerRoomId = function(roomId, subRoomId) {
		return roomId + '_' + subRoomId;
	};

	var index_bindNamespaces = function() {
		var bindNamespace = function(config){
			var namespace = new Namespace(_io, config.name);
			namespace.getIO().on('connect', require('./Handler/SocketHandler'));

			namespace.getIO().getNamespace = function(){
				return namespace;
			};

			//@TODO: THIS SHOULD NOT BE STATIC CODED!!!!!!!
			var room = new Room("4_0");
			namespace.addRoom(room);
			// END

			_namespaces[config.name] = namespace;
		};

		app.settings.namespaces.forEach(bindNamespace);

		delete app.settings.namespaces;
	};

	return app;
}

/**
 * @returns {Function}
 */
module.exports = function() {
	if(_app === null) {
		console.log("new App");
		_app = app();
	}

	return _app;
}();
