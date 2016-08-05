/**
 * @param {number} id
 * @param {string} name
 * @constructor
 */
function Participant(id, name) {

	/**
	 * @type {number}
	 */
	var _id = id;

	/**
	 * @type {string}
	 */
	var _name = name;

	/**
	 * @type {Array}
	 * @private
	 */
	var _sockets = [];

	/**
	 * @returns {number}
	 */
	this.getId = function() { return _id; };

	/**
	 * @returns {string}
	 */
	this.getName = function() { return _name; };

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

	var forSockets = function(callback) {
		for(var key in _sockets) {
			if(_sockets.hasOwnProperty(key)) {
				callback(_sockets[key]);
			}
		}
	}
}

module.exports = exports = Participant;