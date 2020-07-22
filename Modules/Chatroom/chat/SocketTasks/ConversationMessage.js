var Container  = require('../AppContainer');
var HTMLEscape = require('../Helper/HTMLEscape');

module.exports = function(conversationId, userId, message) {
	function shouldPersistMessage(conversation) {
		var doStoreMessage = conversation.isGroup();

		if (!conversation.isGroup()) {
			var participants = conversation.getParticipants();

			for (var index in participants) {
				if (participants.hasOwnProperty(index) && participants[index].getAcceptsMessages()) {
					doStoreMessage = true;
					break;
				}
			}
		}

		return doStoreMessage;
	}

	if (conversationId !== null && userId !== null && message !== null) {

		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);
		var participant = namespace.getSubscriber(userId);

		if(conversation !== null && conversation.isParticipant(participant))
		{
			var messageObj = {
				conversationId: conversationId,
				userId: userId,
				message: HTMLEscape.escape(message),
				timestamp: (new Date).getTime()
			};

			if (participant.getAcceptsMessages()) {
				var doStoreMessage = shouldPersistMessage(conversation);

				if (doStoreMessage) {
					namespace.getDatabase().persistConversationMessage(messageObj);
				}

				conversation.emit('conversation', conversation.json());
				var ignoredParticipants = conversation.send(messageObj);

				if (Object.keys(ignoredParticipants).length > 0) {
					messageObj["ignoredParticipants"] = ignoredParticipants;
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
