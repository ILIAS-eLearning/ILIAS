var Container = require('../AppContainer');
var async = require('async');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	async.eachSeries(conversations, function(conversation, nextLoop){
		var conversationClosed = false;
		namespace.getDatabase().getLatestMessage(conversation, function(row){
			row.userId = row.user_id;
			row.conversationId = row.conversation_id;
			conversation.setLatestMessage(row);
		}, function(){
			namespace.getDatabase().getConversationStateForParticipant(conversation.getId(), socket.participant.getId(), function(row){
				conversationClosed = row.is_closed;
			}, function(){
				if(!conversationClosed) {
					socket.participant.emit('conversation', conversation.json());
				}

				nextLoop();
			});

		}, function(err){
			if(err) throw err;
		});
	});
};