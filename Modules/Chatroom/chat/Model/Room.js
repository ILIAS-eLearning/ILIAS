/**
 * This class represents a room in the chatserver.
 * The id consists of the main room id and the sub room id:
 *
 * @example roomId_subRoomId
 *
 * @param {string} id
 * @constructor
 */
var Room = function Room(id)
{

	/**
	 * @type {string}
	 * @private
	 */
	var _id = id;

	/**
	 * @type {{}}
	 * @typedef {Subscriber}
	 * @private
	 */
	var _subscribers = {};

	/**
	 * @type {string}
	 * @private
	 */
	var _title;

	/**
	 * @type {number}
	 */
	var _ownerId;

	/**
	 * @type {Array}
	 * @private
	 */
	var _joinedSubscribers = [];

	/**
	 * Get the id of the room
	 *
	 * @returns {string}
	 */
	this.getId = function() { return _id; };

	/**
	 * Get the title of the room
	 *
	 * @returns {string}
	 */
	this.getTitle = function() { return _title; };

	/**
	 * Set the title of the room
	 *
	 * @param {string} title
	 */
	this.setTitle = function(title) { _title = title; };

	/**
	 * Get the id of the room owner
	 *
	 * @returns {number}
	 */
	this.getOwnerId = function() { return _ownerId; };

	/**
	 * Set the id of the room owner
	 *
	 * @param ownerId
	 */
	this.setOwnerId = function(ownerId) { _ownerId = ownerId; };

	/**
	 * Checks if the room has subscribers.
	 *
	 * @param {number} id
	 * @returns {boolean}
	 */
	this.hasSubscriber = function(id) {
		return (_subscribers[id] !== undefined);
	};

	this.hasSubscribers = function() {
		return Object.keys(_subscribers).length > 0;
	};

	/**
	 * Adds a subscriber to the room.
	 *
	 * @param {Subscriber} subscriber
	 */
	this.addSubscriber = function(subscriber) {
		if(!this.hasSubscriber(subscriber.getId())) {
			_subscribers[subscriber.getId()] = subscriber;
		}
	};

	/**
	 * Get a subscriber by his id.
	 * Returns null if there is no subscriber for the delivered id in this room.
	 *
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
	 * Removes a subscriber with the delivered id from this room.
	 *
	 * @param {number} id
	 */
	this.removeSubscriber = function(id) {
		if(this.hasSubscriber(id)) {
			delete _subscribers[id];
		}
	};

	this.getSubscribers = function(){
		return _subscribers;
	};

	/**
	 * @returns {{}}
	 */
	this.getJoinedSubscribers = function() {
		var jsonSubscribers = {};

		for(var key in _subscribers) {
			if(this.subscriberHasJoined(key)) {
				jsonSubscribers[key] = {
					id: _subscribers[key].getId(),
					username: _subscribers[key].getName()
				}
			}
		}
		return jsonSubscribers;
	};

	/**
	 * @param {number} id
	 * @returns {boolean}
	 */
	this.subscriberHasJoined = function(id) {
		return _joinedSubscribers.indexOf(parseInt(id)) >= 0;
	};

	/**
	 * @param {number} id
	 */
	this.subsciberJoined = function(id) {
		if(this.hasSubscriber(id) && !this.subscriberHasJoined(id)) {
			_joinedSubscribers.push(parseInt(id));
		}
	};

	/**
	 * @param {number} id
	 */
	this.subscriberLeft = function(id) {
		if(this.subscriberHasJoined(id))
		{
			_joinedSubscribers.splice(_joinedSubscribers.indexOf(id), 1);
		}
	}
};

module.exports = Room;
