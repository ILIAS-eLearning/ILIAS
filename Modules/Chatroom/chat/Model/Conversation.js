const Container = require('../AppContainer');

/**
 *
 * @param {string} id
 * @constructor
 */
const Conversation = function Conversation(id) {
	/**
	 * @type {string}
	 * @private
	 */
	let _id = id;

	/**
	 *
	 * @type {Map<string, Participant>}
	 * @private
	 */
	const _participants = new Map();

	/**
	 * @type {boolean}
	 * @private
	 */
	let _group = false;

	/**
	 *
	 * @type {null|object}
	 * @private
	 */
	let _latestMessage = null;

	/**
	 *
	 * @type {number}
	 * @private
	 */
	let _numNewMessages = 0;

	/**
	 *
	 * @returns {string}
	 */
	this.getId = function () {
		return _id;
	};

	/**
	 *
	 * @param {string[]|number[]} participantIds
	 * @returns {boolean}
	 */
	this.matchesParticipants = function (participantIds) {
		const requestedParticipantIds = new Set(participantIds.map(id => id.toString()));

		const currentParticipantIds = new Set(
			Array.from(_participants.values()).map(participant => participant.getId().toString())
		)

		return 0 === (new Set(
			[...requestedParticipantIds].filter(x => currentParticipantIds.has(x))
		)).size;
	};

	/**
	 *
	 * @param {number} num
	 */
	this.setNumNewMessages = function (num) {
		_numNewMessages = num;
	};

	/**
	 *
	 * @returns {number}
	 */
	this.getNumNewMessages = function () {
		return _numNewMessages;
	};

	/**
	 *
	 * @param message
	 * @returns {Set<number>} Returns a collection of user ids which did not want to receive messages
	 */
	this.send = function (message) {
		const ignoredParticipants = new Set();

		function sendParticipantMessage(participant) {
			if (!participant.getAcceptsMessages()) {
				Container.getLogger().info("Conversation.send: User %s does not want to further receive messages", participant.getId());
				ignoredParticipants.add(participant.getId());
				return;
			}

			participant.send(message);
		}

		forParticipants(sendParticipantMessage);

		return ignoredParticipants;
	};

	/**
	 *
	 * @param event
	 * @param data
	 * @returns {Set<number>} Returns a collection of user ids which did not want to receive messages
	 */
	this.emit = function (event, data) {
		const ignoredParticipants = new Set();

		function emitParticipant(participant) {
			if (!participant.getAcceptsMessages()) {
				Container.getLogger().info("Conversation.emit: User %s does not want to further receive messages", participant.getId());
				ignoredParticipants.add(participant.getId());
				return;
			}

			participant.emit(event, data);
		}

		forParticipants(emitParticipant);

		return ignoredParticipants;
	};

	/**
	 *
	 * @param {Participant} participant
	 */
	this.addParticipant = function (participant) {
		_participants.set(participant.getId().toString(), participant);
		participant.addOrUpdateConversation(this);
	};

	/**
	 *
	 * @param {Participant} participant
	 */
	this.removeParticipant = function (participant) {
		if (_participants.has(participant.getId().toString())) {
			_participants.delete(participant.getId().toString());
		}
		participant.removeConversation(this);
	};

	/**
	 *
	 * @returns {Map<string, Participant>}
	 */
	this.getParticipants = function () {
		return _participants;
	};

	/**
	 *
	 * @returns {boolean}
	 */
	this.isGroup = function () {
		return _group;
	};

	/**
	 *
	 * @param {boolean} status
	 */
	this.setIsGroup = function (status) {
		_group = status;
	};

	/**
	 *
	 * @param {null|object} message
	 */
	this.setLatestMessage = function (message) {
		_latestMessage = message;
	};

	/**
	 *
	 * @param {Participant} participant
	 * @returns {boolean}
	 */
	this.isParticipant = function (participant) {
		return _participants.has(participant.getId().toString());
	};

	/**
	 *
	 * @returns {{latestMessage: null|object, id: string, isGroup: boolean, participants: [], numNewMessages: number}}
	 */
	this.json = function () {
		const participants = [];

		for (let participant of _participants.values()) {
			participants.push(participant.json());
		}

		return {
			id: _id,
			participants: participants,
			latestMessage: _latestMessage,
			numNewMessages: _numNewMessages,
			isGroup: _group
		};
	};

	/**
	 *
	 * @param {function(Participant) : void} callback
	 */
	function forParticipants(callback) {
		for (let participant of _participants.values()) {
			callback(participant);
		}
	}
};

module.exports = Conversation;
