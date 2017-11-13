var Container = require('../AppContainer');
var async = require('async');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	var onError = function(err){
		if(err) {
			throw err;
		}
	};

	var callback = function(conversation, nextLoop){
		var conversationClosed = false;
		var onResult = function(row) {
			conversationClosed = row.is_closed;
		};

		var onEnd = function() {
			if (!conversationClosed) {
				var countUnreadMessages = function () {
					var startNextLoop = function(){
						socket.participant.emit('conversation', conversation.json());
						nextLoop();
					};

					var setNumberOfNewMessages = function(row){
						conversation.setNumNewMessages(row.numMessages);
					};

					namespace.getDatabase().countUnreadMessages(conversation.getId(), socket.participant.getId(), setNumberOfNewMessages, startNextLoop);
				};

				var setLatestMessage = function (row) {
					row.userId = row.user_id;
					row.conversationId = row.conversation_id;
					conversation.setLatestMessage(row);
				};

				namespace.getDatabase().getLatestMessage(conversation, setLatestMessage, countUnreadMessages);
			} else {
				nextLoop();
			}
		};

		namespace.getDatabase().getConversationStateForParticipant(conversation.getId(), socket.participant.getId(), onResult, onEnd);
	};

	async.eachSeries(conversations, callback, onError);
};