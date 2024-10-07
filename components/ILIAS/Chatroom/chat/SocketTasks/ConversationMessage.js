/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

var Container  = require('../AppContainer');
var UUID = require('node-uuid');

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
				message: message,
				timestamp: (new Date).getTime(),
				id: UUID.v4()
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
