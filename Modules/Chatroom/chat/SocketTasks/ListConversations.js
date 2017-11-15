var Container = require('../AppContainer');
var async = require('async');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	var onConversationListResult = function(conversation, nextLoop){
		var conversationClosed = false;

		function setConservationState(row) {
			conversationClosed = row.is_closed;
		}

		function fetchLatestMessageForOpenConversation() {
			if (!conversationClosed) {
				function setLatestMessageOnConversation(row) {
					row.userId         = row.user_id;
					row.conversationId = row.conversation_id;
					conversation.setLatestMessage(row);
				}

				function determineUnreadMessages() {
					function setNumberOfNewMessages(row) {
						conversation.setNumNewMessages(row.numMessages);
					}

					var emitConversationAndContinue = function emitConversationAndContinue() {
						socket.participant.emit('conversation', conversation.json());
						nextLoop();
					};

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
			} else {
				nextLoop();
			}
		}

		namespace.getDatabase().getConversationStateForParticipant(
			conversation.getId(),
			socket.participant.getId(),
			setConservationState,
			fetchLatestMessageForOpenConversation
		);
	};

	function onPossibleConversationListError(err) {
		if (err) {
			throw err;
		}
	}

	async.eachSeries(conversations, onConversationListResult, onPossibleConversationListError);
};
