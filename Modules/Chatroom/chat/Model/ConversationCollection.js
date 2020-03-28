const ConversationCollection = function ConversationCollection() {
	/**
	 *
	 * @type {Map<string, Conversation>}
	 * @private
	 */
	let _collection = new Map();

	/**
	 *
	 * @returns {Map<string, Conversation>}
	 */
	this.all = function () {
		return _collection;
	};

	/**
	 *
	 * @param {Conversation} conversation
	 */
	this.add = function (conversation) {
		_collection.set(conversation.getId().toString(), conversation);
	};

	/**
	 *
	 * @param {string|number} conversationId
	 * @returns {Conversation|null}
	 */
	this.getById = function (conversationId) {
		if (_collection.has(conversationId.toString())) {
			return _collection.get(conversationId.toString());
		}

		return null;
	};

	/**
	 *
	 * @param {string[]|number[]} participantIds
	 * @returns {Conversation|null}
	 */
	this.getForParticipants = function (participantIds) {
		for (let conversation of _collection.values()) {
			if (conversation.matchesParticipants(participantIds)) {
				return conversation;
			}
		}

		return null;
	};
};

module.exports = ConversationCollection;