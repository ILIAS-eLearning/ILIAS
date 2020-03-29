const Container = require('../AppContainer'),
	HTMLEscape = require('../Helper/HTMLEscape'),
	UUID = require('node-uuid');

/**
 *
 * @param {string} conversationId
 * @param {number} userId
 * @param {object} message
 */
module.exports = function (conversationId, userId, message) {
	/**
	 *
	 * @param  {Conversation} conversation
	 * @returns {boolean}
	 */
	function shouldPersistMessage(conversation) {
		let doStoreMessage = conversation.isGroup();

		if (!doStoreMessage) {
			doStoreMessage = true;
			for (let participant of conversation.getParticipants().values()) {
				if (!participant.getAcceptsMessages()) {
					doStoreMessage = false;
					break;
				}
			}
		}

		return doStoreMessage;
	}

	if (conversationId != null && userId != null && message !== null) {
		const namespace = Container.getNamespace(this.nsp.name),
			conversation = namespace.getConversations().getById(conversationId),
			participant = namespace.getSubscriber(userId);

		if (conversation !== null && participant !== null && conversation.isParticipant(participant)) {
			const messageObj = {
				conversationId: conversationId,
				userId: userId,
				message: HTMLEscape.escape(message),
				timestamp: (new Date).getTime(),
				id: UUID.v4()
			};

			if (participant.getAcceptsMessages()) {
				if (shouldPersistMessage(conversation)) {
					namespace.getDatabase().persistConversationMessage(messageObj);
				}

				conversation.emit('conversation', conversation.json());
				let ignoredParticipants = conversation.send(messageObj);

				if (ignoredParticipants.size > 0) {
					messageObj["ignoredParticipants"] = Array.from(ignoredParticipants);
					participant.emit("participantsSuppressedMessages", messageObj);
				}

				Container.getLogger().info('SendMessage by "%s" in conversation %s', userId, conversationId);
			} else {
				participant.emit("senderSuppressesMessages", messageObj);

				Container.getLogger().info('SendMessage by "%s" in conversation %s not delivered, user does not want to receive messages', userId, conversationId);
			}
		}
	}
};
