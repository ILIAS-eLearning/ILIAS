
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
	var _closed = false;

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
			if(participants.hasOwnProperty(index) && _participants.indexOf(participants[index].id) === -1)
			{
				return false;
			}
		}
		return true;
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

	this.isClosed = function() {
		return _closed;
	};

	this.setIsClose = function(isClosed) {
		_closed = isClosed;
	};
};

module.exports = Conversation;