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

	this.setArguments = function setArguments(arguments) { _arguments = arguments; };

	this.getArguments = function getArguments() { return _arguments; };

	this.getArgument = function getArgument(index) {
		if(_arguments.hasOwnProperty(index)) {
			return _arguments[index];
		}
	};
	this.setServerConfig = function setServerConfig(config) { _serverConfig = config; };
	this.getServerConfig = function getServerConfig(){ return _serverConfig; };
	this.addClientConfig = function addClientConfig(config){ _clientConfigs.push(config); };
	this.getClientConfigs = function getClientConfigs() { return _clientConfigs;	};
	this.getClientConfig = function getClientConfig(name) {
		for(var index in _clientConfigs) {
			if(_clientConfigs.hasOwnProperty(index) && _clientConfigs[index].name == name) {
				return _clientConfigs[index];
			}
		}
		return null;
	};
	this.setApi = function setApi(api) { _api = api; };
	this.getApi = function getApi() { return _api; };
	this.addNamespace = function addNamespace(namespace) { _namespaces.push(namespace); };
	this.getNamespaces = function getNamespaces() { return _namespaces; };
	this.getNamespace = function getNamespace(name) {
		var namespace = null;
		name = name.replace(/^\//, '');

		var setNamespace = function setNamespace(element){
			if(element.getName() == name) {
				namespace = element;
				return true;
			}
		};

		_namespaces.forEach(setNamespace);
		return namespace;
	};
	this.createServerRoomId = function createServerRoomId(roomId, subRoomId) {
		return roomId + '_' + subRoomId;
	};
	this.splitServerRoomId = function splitServerRoomId(roomId) {
		return roomId.split('_');
	};

	this.setServer = function setServer(server) { _server = server; };
	this.getServer = function getServer() { return _server; };

	this.setTimeout = function setTimeout(subscriberId, callback, delay) {
		var timeout = setTimeout(callback, delay);

		_timeouts[subscriberId] = timeout;
	};

	this.removeTimeout = function removeTimeout(subscriberId) {
		if (_timeouts.hasOwnProperty(subscriberId)) {
			clearTimeout(_timeouts[subscriberId]);
			delete _timeouts[subscriberId];
		}
	};

	/**
	 * @returns {Logger}
	 */
	this.getLogger = function getLogger() { return _logger; };
	this.setLogger = function setLogger(logger) { _logger = logger; };
};

/**
 * @type {AppContainer|null}
 * @private
 */
var _instance = null;

var getInstance = function getInstance() {
	if(_instance == null) {
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
