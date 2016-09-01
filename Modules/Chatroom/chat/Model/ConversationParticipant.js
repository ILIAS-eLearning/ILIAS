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

	var _conversations = [];

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

	this.emit = function(event, data) {
		forSockets(function(socket){
			//console.log("emit", event, data);
			socket.emit(event, data);
		})
	};

	this.join = function(name) {
		if(this.isOnline()) {
			forSockets(function(socket){
				socket.join(name);
			});
		}
	};

	this.leave = function(name) {
		if(this.isOnline()) {
			forSockets(function(socket){
				socket.leave(name);
			});
		}
	};

	this.send = function(message) {
		if(this.isOnline()) {
			forSockets(function(socket){
				socket.emit('message', message)
			});
		}
	};

	this.json = function() {
		return {
			id: _id,
			name: _name
		}
	};

	this.addConversation = function(conversation) {
		_conversations.push(conversation);
	};

	this.removeConversation = function(conversation) {
		var conversationIndex = getConversationIndex(conversation, _conversations);
		if (conversationIndex !== false) {
			_conversations.splice(conversationIndex, 1);
		}
	};

	this.getConversations = function() {
		return _conversations;
	};

	var getConversationIndex = function(conversation, conversations) {
		for (var key in conversations) {
			if (conversations.hasOwnProperty(key)) {
				var id = conversations[key].getId();

				if (id == conversation.getId()) {
					return key;
				}
			}
		}
		return false;
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
	};
}

module.exports = exports = Participant;