/**
 * @class Namespace
 * @param {engine.io/Server} io
 * @param {string} name
 * @constructor
 */
var Namespace = function Namespace(name)
{

	/**
	 * @type {string}
	 * @private
	 */
	var _name = name;

	/**
	 * @type {engine.io/Server}
	 * @private
	 */
	var _io;

	/**
	 *
	 * @type {JSON}
	 * @typedef {Room}
	 * @private
	 */
	var _rooms = {};

	/**
	 * @type {JSON}
	 * @typedef {Subscriber}
	 * @private
	 */
	var _subscribers = {};

	var _database;

	/**
	 * @returns {string}
	 */
	this.getName = function() { return _name; };

	/**
	 * @returns {engine.io|Server}
	 */
	this.getIO = function() { return _io; };
	this.setIO = function(io) { _io = io; };

	/**
	 * @param {string} id
	 * @returns {boolean}
	 */
	this.hasRoom = function(id) {
		return (_rooms[id] !== undefined);
	};

	this.getRooms = function() { return _rooms; };

	/**
	 * @param {string} id
	 */
	this.removeRoom = function(id) {
		if(this.hasRoom(id)) {
			delete _rooms[id];
		}
	};

	/**
	 * @param {Room} room
	 */
	this.addRoom = function(room) {
		if(!this.hasRoom(room.getId())) {
			_rooms[room.getId()] = room;
		}

	};

	/**
	 * @param {string} id
	 * @returns {Room}
	 */
	this.getRoom = function(id) {
		if(this.hasRoom(id)) {
			return _rooms[id];
		}
		return null;
	};

	/**
	 * @returns {string}
	 */
	this.getId = function() { return _id; };

	/**
	 * @param {number} id
	 * @returns {boolean}
	 */
	this.hasSubscriber = function(id) {
		return (_subscribers[id] !== undefined);
	};

	/**
	 * @param {Subscriber} subscriber
	 */
	this.addSubscriber = function(subscriber) {
		if(!this.hasSubscriber(subscriber.getId())) {
			_subscribers[subscriber.getId()] = subscriber;
		}
	};

	/**
	 * @param {number} id
	 * @returns {Subscriber|null}
	 */
	this.getSubscriber = function(id) {
		if(this.hasSubscriber(id)) {
			return _subscribers[id];
		}
		return null;
	};

	/**
	 * @param {string} id
	 */
	this.removeSubscriber = function(id) {
		if(this.hasSubscriber(id)) {
			delete _subscribers[id];
		}
	};



	this.setDatabase = function(database) {
		_database = database;
	};

	/**
	 * @returns {Database}
	 */
	this.getDatabase = function() {
		return _database;
	};

	this.disconnectSockets = function() {
		if(this.getIO() != undefined) {
			var sockets = this.getIO().sockets;

			for(var key in sockets) {
				if(sockets.hasOwnProperty(key)) {
					this.getIO().to(key).emit('shutdown');
				}
			}
		}
	}
};


module.exports = Namespace;