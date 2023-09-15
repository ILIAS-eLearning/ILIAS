/**
 * @param {number} id
 * @constructor
 */
function Subscriber(id) {

	/**
	 * @type {number}
	 */
	var _id = id;

	/**
	 * @type {string}
	 */
	var _name;

	/**
	 * @type {Array}
	 * @private
	 */
	var _socketIds = [];

	/**
	 * @returns {number}
	 */
	this.getId = function() { return _id; };

	/**
	 * @param {string} name
	 */
	this.setName = function(name) { _name = name;};

	/**
	 * @returns {string}
	 */
	this.getName = function() { return _name; };

	/**
	 * @param {string} socketId
	 */
	this.addSocketId = function(socketId) {
		if(!this.hasSocketId(socketId)) {
			_socketIds.push(socketId);
		}
	};

	/**
	 * @returns {Array}
	 */
	this.getSocketIds = function() {
		return _socketIds;
	};

	/**
	 * @param {string} id
	 * @returns {boolean}
	 */
	this.hasSocketId = function(id) {
		return _socketIds.indexOf(id) > -1;
	};

	this.removeSocketId = function(id) {
		if(this.hasSocketId(id)) {
			_socketIds.splice(_socketIds.indexOf(id), 1);
		}
	};

	/**
	 * @returns {string}
	 */
	this.toString = function() {
		var json = {
			id: this.getId(),
			username: this.getName()
		};

		return JSON.stringify(json);
	}
}

module.exports = exports = Subscriber;
