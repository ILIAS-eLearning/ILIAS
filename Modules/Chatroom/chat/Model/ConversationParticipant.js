/**
 * @param {number} id
 * @param {string} name
 * @constructor
 */
const Participant = function Participant(id, name) {

	/**
	 * @type {number}
	 * @private
	 */
	let _id = id;

	/**
	 * @type {string}
	 * @private
	 */
	let _name = name;

	/**
	 * @type {boolean}
	 * @private
	 */
	let _online = false;

	/**
	 *
	 * @type {boolean}
	 * @private
	 */
	let _acceptsMessages = true;

	/**
	 *
	 * @type {Map<string, Conversation>}
	 * @private
	 */
	const _conversations = new Map();

	/**
	 * @returns {number}
	 */
	this.getId = function () {
		return _id;
	};

	/**
	 * @param {string} name
	 */
	this.setName = function (name) {
		_name = name;
	};

	/**
	 * @returns {string}
	 */
	this.getName = function () {
		return _name;
	};

	/**
	 * @returns {boolean}
	 */
	this.isOnline = function () {
		return _online;
	};

	/**
	 * @param {boolean} isOnline
	 */
	this.setOnline = function (isOnline) {
		_online = isOnline;
	};

	/**
	 * @param {boolean} status
	 */
	this.setAcceptsMessages = function (status) {
		_acceptsMessages = status;
	};

	/**
	 * @returns {boolean}
	 */
	this.getAcceptsMessages = function () {
		return _acceptsMessages;
	};

	this.removeSocket = function (socket) {
		const index = _sockets.indexOf(socket);
		if (index > -1) {
			_sockets.splice(index, 1);
		}
	};

	this.addSocket = function (socket) {
		_sockets.push(socket);
	};

	function createEmitDataOnSocketCallback(event, data) {
		return function emitDataOnSocket(socket) {
			socket.emit(event, data);
		};
	}

	/**
	 *
	 * @param {string} name
	 * @returns {joinSocket}
	 */
	function createJoinSocketCallback(name) {
		return function joinSocket(socket) {
			socket.join(name);
		};
	}

	/**
	 *
	 * @param {string} name
	 * @returns {leaveSocket}
	 */
	function createLeaveSocketCallback(name) {
		return function leaveSocket(socket) {
			socket.leave(name);
		};
	}

	/**
	 *
	 * @param {object} message
	 * @returns {emitMessageOnSocket}
	 */
	function createEmitMessageOnSocketCallback(message) {
		return function emitMessageOnSocket(socket) {
			socket.emit('message', message);
		};
	}

	this.emit = function (event, data) {
		forSockets(createEmitDataOnSocketCallback(event, data));
	};

	/**
	 *
	 * @param {string} name
	 */
	this.join = function (name) {
		if (this.isOnline()) {
			forSockets(createJoinSocketCallback(name));
		}
	};

	/**
	 *
	 * @param {string} name
	 */
	this.leave = function (name) {
		if (this.isOnline()) {
			forSockets(createLeaveSocketCallback(name));
		}
	};

	/**
	 *
	 * @param {object} message
	 */
	this.send = function (message) {
		if (this.isOnline()) {
			forSockets(createEmitMessageOnSocketCallback(message));
		}
	};

	/**
	 *
	 * @returns {{name: string, id: number}}
	 */
	this.json = function () {
		return {
			id: _id,
			name: _name
		}
	};

	/**
	 *
	 * @param {Conversation} conversation
	 */
	this.addOrUpdateConversation = function (conversation) {
		_conversations.set(conversation.getId().toString(), conversation);
	};

	/**
	 *
	 * @param {Conversation} conversation
	 */
	this.removeConversation = function (conversation) {
		if (_conversations.has(conversation.getId().toString())) {
			_conversations.delete(conversation.getId().toString());
		}
	};

	/**
	 *
	 * @returns {Map<string, Conversation>}
	 */
	this.getConversations = function () {
		return _conversations;
	};

	/**
	 * @param {Function} callback
	 * @private
	 */
	function forSockets(callback) {
		for (let key in _sockets) {
			if (_sockets.hasOwnProperty(key)) {
				callback(_sockets[key]);
			}
		}
	}
};

module.exports = Participant;
