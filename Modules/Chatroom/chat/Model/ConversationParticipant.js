/**
 * @param {number} id
 * @param {string} name
 * @constructor
 */
function Participant(id, name) {

	/**
	 * @type {number}
	 * @private
	 */
	var _id = id;

	/**
	 * @type {string}
	 * @private
	 */
	var _name = name;

	/**
	 * @type {boolean}
	 * @private
	 */
	var _online = false;

	/**
	 * @type {Array}
	 * @private
	 */
	var _sockets = [];

	/**
	 * @returns {number}
	 */
	this.getId = function() {
		return _id;
	};

	/**
	 * @param {string} name
	 */
	this.setName = function(name) {
		_name = name;
	};

	/**
	 * @returns {string}
	 */
	this.getName = function() {
		return _name;
	};

	/**
	 * @returns {boolean}
	 */
	this.isOnline = function() {
		return _online;
	};

	/**
	 * @param {boolean} isOnline
	 */
	this.setOnline = function(isOnline) {
		_online = isOnline;
	};

	this.removeSocket = function(socket) {
		var index = _sockets.indexOf(socket);
		if(index > -1) {
			_sockets.splice(index, 1);
		}
	};

	this.addSocket = function(socket) {
		_sockets.push(socket);
	};

	this.join = function(name) {
		forSockets(function(socket){
			socket.join(name);
		});
	};

	this.send = function(message) {
		forSockets(function(socket){
			socket.emit('message', message)
		});
	};

	/**
	 * @param {Function} callback
	 * @private
	 */
	var forSockets = function(callback) {
		for(var key in _sockets) {
			if(_sockets.hasOwnProperty(key)) {
				callback(_sockets[key]);
			}
		}
	}
}

module.exports = exports = Participant;