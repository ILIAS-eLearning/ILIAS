var Container = require('../AppContainer');
var async = require('async');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	var onConversationListResult = function(conversation, nextLoop){
		var conversationClosed = false;

		var setConservationState = function(row) {
			conversationClosed = row.is_closed;
		};

		var fetchLatestMessageForOpenConversation = function() {
			if (!conversationClosed) {
				var onMessageRowFound = function () {
					var setNumberOfNewMessages = function(row) {
						conversation.setNumNewMessages(row.numMessages);
					};

					var emitConversationAndContinue = function() {
						socket.participant.emit('conversation', conversation.json());
						nextLoop();
					};

					namespace.getDatabase().countUnreadMessages(
						conversation.getId(),
						socket.participant.getId(),
						setNumberOfNewMessages,
						emitConversationAndContinue
					);
				};

				var setLatestMessageOnConversation = function (row) {
					row.userId         = row.user_id;
					row.conversationId = row.conversation_id;
					conversation.setLatestMessage(row);
				};

				namespace.getDatabase().getLatestMessage(
					conversation,
					onMessageRowFound,
					setLatestMessageOnConversation
				);
			} else {
				nextLoop();
			}
		};

		namespace.getDatabase().getConversationStateForParticipant(
			conversation.getId(),
			socket.participant.getId(),
			setConservationState,
			fetchLatestMessageForOpenConversation
		);
	};

	var onPossibleConversationListError = function(err) {
		if (err) {
			throw err;
		}
	};

	async.eachSeries(conversations, onConversationListResult, onPossibleConversationListError);
};