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
	 * 
	 * @type {boolean}
	 * @private
	 */
	var _acceptsMessages = true;

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

	/**
	 * @param {boolean} status
	 */
	this.setAcceptsMessages = function(status) {
		_acceptsMessages = status;
	};

	/**
	 * @returns {boolean}
	 */
	this.getAcceptsMessages = function() {
		return _acceptsMessages;
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

	function createEmitDataOnSocketCallback(event, data) {
		return function emitDataOnSocket(socket){
			socket.emit(event, data);
		};
	}

	function createJoinSocketCallback(name) {
		return function joinSocket(socket){
			socket.join(name);
		};
	}

	function createLeaveSocketCallback(name) {
		return function leaveSocket(socket){
			socket.leave(name);
		};
	}

	function createEmitMessageOnSocketCallback(message) {
		return function emitMessageOnSocket(socket){
			socket.emit('message', message);
		};
	}

	this.emit = function(event, data) {
		var emitDataOnSocket = createEmitDataOnSocketCallback(event, data);
		forSockets(emitDataOnSocket);
	};

	this.join = function(name) {
		if(this.isOnline()) {
			var joinSocket = createJoinSocketCallback(name);
			forSockets(joinSocket);
		}
	};

	this.leave = function(name) {
		if(this.isOnline()) {
			var leaveSocket = createLeaveSocketCallback(name);
			forSockets(leaveSocket);
		}
	};

	this.send = function(message) {
		if(this.isOnline()) {
			var emitMessageOnSocket = createEmitMessageOnSocketCallback(message);
			forSockets(emitMessageOnSocket);
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
		let conversationIndex = getConversationIndex(conversation, _conversations);
		if (conversationIndex !== -1) {
			_conversations.splice(conversationIndex, 1);
		}
	};

	this.getConversations = function() {
		return _conversations;
	};

	/**
	 * 
	 * @param conversation
	 * @param conversations
	 * @returns {number}
	 */
	function getConversationIndex(conversation, conversations) {
		const index =  conversations.findIndex(function(val) {
			if (null === val) {
				return false;
			}

			return val.getId().toString() === conversation.getId().toString();
		});

		return index;
	}

	/**
	 * @param {Function} callback
	 * @private
	 */
	function forSockets(callback) {
		for(var key in _sockets) {
			if(_sockets.hasOwnProperty(key)) {
				callback(_sockets[key]);
			}
		}
	}
}

module.exports = exports = Participant;
