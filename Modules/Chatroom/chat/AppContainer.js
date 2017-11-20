var async = require('async');

/**
 * Singleton AppContainer
 */
var AppContainer = function AppContainer() {

	/**
	 * Stores Commandline Arguments
	 *
	 * @type {Array}
	 * @private
	 */
	var _arguments = [];

	/**
	 * @type {JSON}
	 * @private
	 */
	var _serverConfig = {};

	/**
	 * @type {Array}
	 * @private
	 */
	var _clientConfigs = [];

	var _namespaces = [];

	var _api;

	var _server;

	var _timeouts = {};

	/**
	 * @type {Logger}
	 */
	var _logger;

	this.setArguments = function(arguments) { _arguments = arguments; };

	this.getArguments = function() { return _arguments; };

	this.getArgument = function(index) {
		if(_arguments.hasOwnProperty(index)) {
			return _arguments[index];
		}
	};
	this.setServerConfig = function(config) { _serverConfig = config; };
	this.getServerConfig = function(){ return _serverConfig; };
	this.addClientConfig = function(config){ _clientConfigs.push(config); };
	this.getClientConfigs = function() { return _clientConfigs;	};
	this.getClientConfig = function(name) {
		for(var index in _clientConfigs) {
			if(_clientConfigs.hasOwnProperty(index) && _clientConfigs[index].name == name) {
				return _clientConfigs[index];
			}
		}
		return null;
	};
	this.setApi = function(api) { _api = api; };
	this.getApi = function() { return _api; };
	this.addNamespace = function(namespace) { _namespaces.push(namespace); };
	this.getNamespaces = function() { return _namespaces; };
	this.getNamespace = function getNamespace(name) {
		var namespace = null;
		name = name.replace(/^\//, '');

		function setNamespace(element){
			if(element.getName() == name) {
				namespace = element;
				return true;
			}
		}

		_namespaces.forEach(setNamespace);
		return namespace;
	};

	this.createServerRoomId = function(roomId, subRoomId) {
		return roomId + '_' + subRoomId;
	};
	this.splitServerRoomId = function(roomId) {
		return roomId.split('_');
	};

	this.setServer = function(server) { _server = server; };
	this.getServer = function() { return _server; };

	this.setTimeout = function(subscriberId, callback, delay) {
		_timeouts[subscriberId] = setTimeout(callback, delay);
	};

	this.removeTimeout = function(subscriberId) {
		if (_timeouts.hasOwnProperty(subscriberId)) {
			clearTimeout(_timeouts[subscriberId]);
			delete _timeouts[subscriberId];
		}
	};

	/**
	 * @returns {Logger}
	 */
	this.getLogger = function() { return _logger; };
	this.setLogger = function(logger) { _logger = logger; };
};

/**
 * @type {AppContainer|null}
 * @private
 */
var _instance = null;

function getInstance() {
	if(_instance === null) {
		_instance = new AppContainer();
	}

	return _instance;
};

/**
 * Returns a Singleton of AppContainer
 *
 * @type {AppContainer}
 */
module.exports = getInstance();
