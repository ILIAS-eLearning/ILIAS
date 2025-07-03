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
 */

var Container = require('../AppContainer');
const sync = require('../Helper/sync');

module.exports = function() {
	Container.getLogger().debug('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	function onConversationListResult(conversation, nextLoop){
		var conversationClosed = false;

		function setConservationState(row) {
			conversationClosed = row.is_closed;
		}

		function fetchLatestMessageForOpenConversation() {
			function setLatestMessageOnConversation(row) {
				row.userId         = row.user_id;
				row.conversationId = row.conversation_id;
				conversation.setLatestMessage(row);
			}

			function determineUnreadMessages() {
				function setNumberOfNewMessages(row) {
					conversation.setNumNewMessages(row.numMessages);
				}

				function emitConversationAndContinue() {
					if (!conversationClosed || (conversation.getNumNewMessages() > 0 && !conversation.isGroup())) {
						socket.participant.emit('conversation', conversation.json());
					}
					nextLoop();
				}

				namespace.getDatabase().countUnreadMessages(
					conversation.getId(),
					socket.participant.getId(),
					setNumberOfNewMessages,
					emitConversationAndContinue
				);
			}

			namespace.getDatabase().getLatestMessage(
				conversation,
				setLatestMessageOnConversation,
				determineUnreadMessages
			);
		}

		namespace.getDatabase().getConversationStateForParticipant(
			conversation.getId(),
			socket.participant.getId(),
			setConservationState,
			fetchLatestMessageForOpenConversation
		);
	}

	sync.each(conversations, onConversationListResult);
};
