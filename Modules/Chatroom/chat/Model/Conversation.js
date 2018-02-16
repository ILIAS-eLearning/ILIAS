var Container  = require('../AppContainer');

var Conversation = function Conversation(id, participants)
{

	/**
	 * @type {string}
	 * @private
	 */
	var _id = id;

	/**
	 * @type {Array}
	 * @private
	 */
	var _participants = participants ? participants : [];

	/**
	 * @type {boolean}
	 * @private
	 */
	var _group = false;

	var _latestMessage = null;

	var _numNewMessages = 0;

	/**
	 * Returns the ID of the conversation;
	 *
	 * @returns {*}
	 */
	this.getId = function() {
		return _id;
	};

	this.matchesParticipants = function(participants) {
		for(var index in _participants)
		{
			if(_participants.hasOwnProperty(index) && !hasParticipant(_participants[index], participants))
			{
				return false;
			}
		}
		return true;
	};

	this.setNumNewMessages = function(num) {
		_numNewMessages = num;
	};

	this.getNumNewMessages = function() {
		return _numNewMessages;
	};

	/**
	 * @param message
	 * @return object Returns a collection of users who did not want to receive messages 
	 */
	this.send = function(message) {

		var ignoredParticipants = {};

		function sendParticipantMessage(participant) {
			if (!participant.getAcceptsMessages()) {
				Container.getLogger().info("Conversation.send: User %s does not want to further receive messages", participant.getId());
				ignoredParticipants[participant.getId()] = participant.getId();
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
	 * @return object Returns a collection of users who did not want to receive messages
	 */
	this.emit = function(event, data) {
		var ignoredParticipants = {};

		function emitParticipant(participant){
			if (!participant.getAcceptsMessages()) {
				Container.getLogger().info("Conversation.emit: User %s does not want to further receive messages", participant.getId());
				ignoredParticipants[participant.getId()] = participant.getId();
				return;
			}

			participant.emit(event, data);
		}

		forParticipants(emitParticipant);

		return ignoredParticipants;
	};

	this.addParticipant = function(participant) {
		if (!hasParticipant(participant, _participants)) {
			_participants.push(participant);
			participant.addConversation(this);
		}
	};

	this.removeParticipant = function(participant) {
		var participantIndex = getParticipantIndex(participant, _participants);
		if (participantIndex !== false) {
			_participants.splice(participantIndex, 1);
			participant.removeConversation(this);
		}
	};

	this.getParticipants = function() {
		return _participants;
	};

	this.isGroup = function() {
		return _group;
	};

	this.setIsGroup = function(isGroup) {
		_group = isGroup;
	};

	this.setLatestMessage = function(message) {
		_latestMessage = message;
	};

	this.isParticipant = function(participant) {
		return hasParticipant(participant, _participants);
	};

	this.json = function() {
		var participants = [];

		for(var key in _participants) {
			participants.push(_participants[key].json());
		}

		return {
			id: _id,
			participants: participants,
			latestMessage: _latestMessage,
			numNewMessages: _numNewMessages,
			isGroup: _group
		};
	};

	function forParticipants(callback) {
		for(var key in _participants) {
			if(_participants.hasOwnProperty(key)) {
				callback(_participants[key]);
			}
		}
	}

	function getParticipantIndex(participant, participants) {
		for (var key in participants) {
			if (participants.hasOwnProperty(key)) {
				var id = participants[key].id;

				if (typeof participants[key].getId === 'function'){
					id = participants[key].getId();
				}

				if (id == participant.getId()) {
					return key;
				}
			}
		}
		return false;
	}

	function hasParticipant(participant, participants) {
		for(var key in participants) {
			if(participants.hasOwnProperty(key)) {
				var id = participants[key].id;

				if(typeof participants[key].getId === 'function'){
					id = participants[key].getId();
				}

				if(id > 0 && id == participant.getId()) {
					return true;
				}
			}

		}
		return false;
	}
};

module.exports = Conversation;
