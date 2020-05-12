

var ConversationCollection = function ConversationCollection() {
	/**
	 *
	 * @type {JSON}
	 * @private
	 */
	var _collection = {};

	this.all = function() {
		return _collection;
	};

	this.add = function(conversation) {
		_collection[conversation.getId()] = conversation;
	};

	this.getById = function(conversationId) {
		if(_collection.hasOwnProperty(conversationId)) {
			return _collection[conversationId];
		}

		return null;
	};

	this.getForParticipants = function(participants) {
		for (let id in _collection) {
			if (_collection.hasOwnProperty(id)) {
				let conversation = _collection[id];

				if (conversation.matchesParticipants(participants)) {
					return conversation;
				}
			}
		}

		return null;
	};
};

module.exports = ConversationCollection;