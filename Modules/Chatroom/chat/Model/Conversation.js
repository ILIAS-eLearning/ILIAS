
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

	/**
	 * @type {boolean}
	 * @private
	 */
	var _opened = true;

	var _messages = [];

	var _lastMessageTimestamp = null;

	/**
	 * Returns the ID of the conversation;
	 *
	 * @returns {*}
	 */
	this.getId = function() {
		return _id;
	};

	this.getLastMessageTimestamp = function() {
		return _lastMessageTimestamp;
	};

	this.setLastMessageTimestamp = function(timestamp) {
		_lastMessageTimestamp = timestamp;
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

	this.addHistory = function(message) {
		console.log(this.getLastMessageTimestamp(), message.timestamp);
		if(this.getLastMessageTimestamp() == null || this.getLastMessageTimestamp() > message.timestamp) {
			this.setLastMessageTimestamp(message.timestamp);
		}

		_messages.push(message);
	};

	this.send = function(message) {
		this.addHistory(message);

		forParticipants(function(participant){
			participant.send(message);
		});
	};

	this.emit = function(event, data) {
		forParticipants(function(participant){
			participant.emit(event, data);
		});
	};

	this.addParticipant = function(participant) {
		if(!hasParticipant(participant, _participants))
		{
			_participants.push(participant);
			participant.addConversation(this);
		}
	};

	this.setOpen = function(isOpen){
		_opened = isOpen;
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


	this.json = function() {
		var participants = [];

		for(var key in _participants) {
			participants.push(_participants[key].json());
		}

		return {
			id: _id,
			participants: participants,
			//open: _opened,
			messages: _messages,
			latestMessageTimestamp: _lastMessageTimestamp
		}
	};

	var forParticipants = function(callback) {
		for(var key in _participants) {
			if(_participants.hasOwnProperty(key)) {
				callback(_participants[key]);
			}
		}
	};

	var hasParticipant = function(participant, participants) {
		for(var key in participants) {
			if(participants.hasOwnProperty(key)) {
				var id = participants[key].id;

				if(typeof participants[key].getId == 'function'){
					id = participants[key].getId();
				}

				if(id == participant.getId()) {
					return true;
				}
			}

		}
		return false;
	}
};

module.exports = Conversation;