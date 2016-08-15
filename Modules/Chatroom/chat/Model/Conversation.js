
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

	var _hasMessages = false;

	/**
	 * Returns the ID of the conversation;
	 *
	 * @returns {*}
	 */
	this.getId = function() {
		return _id;
	};

	this.matchesParticipants = function(participants) {
		for(var index in participants)
		{
			if(participants.hasOwnProperty(index) && !hasParticipant(participants[index]))
			{
				return false;
			}
		}
		return true;
	};

	this.send = function(message) {
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
		if(_participants.indexOf(participant) === -1)
		{
			_participants.push(participant);
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


	this.json = function() {
		var participants = [];

		for(var key in _participants) {
			participants.push(_participants[key].json());
		}

		return {
			id: _id,
			participants: participants,
			open: _opened,
			hasMessages: _hasMessages
		}
	};

	var forParticipants = function(callback) {
		for(var key in _participants) {
			if(_participants.hasOwnProperty(key)) {
				console.log(_participants[key].isOnline());
				callback(_participants[key]);
			}
		}
	};

	var hasParticipant = function(participant) {
		for(var key in _participants) {
			if(_participants.hasOwnProperty(key) && _participants[key].getId() == participant.id) {
				return true;
			}
		}
		return false;
	}
};

module.exports = Conversation;