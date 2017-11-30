var Container = require('../AppContainer');
var async = require('async');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	async.eachSeries(conversations, function(conversation, nextLoop){
		var conversationClosed = false;
		namespace.getDatabase().getConversationStateForParticipant(conversation.getId(), socket.participant.getId(), function(row){
			conversationClosed = row.is_closed;
		}, function() {
			namespace.getDatabase().getLatestMessage(conversation, function (row) {
				row.userId = row.user_id;
				row.conversationId = row.conversation_id;
				conversation.setLatestMessage(row);
			}, function () {
				namespace.getDatabase().countUnreadMessages(conversation.getId(), socket.participant.getId(), function(row){
					conversation.setNumNewMessages(row.numMessages);
				}, function(){
					if (!conversationClosed || (conversation.getNumNewMessages() > 0 && !conversation.isGroup())) {
						socket.participant.emit('conversation', conversation.json());
					}
					nextLoop();
				});
			});
		});
	}, function(err){
		if(err) throw err;
	});
};