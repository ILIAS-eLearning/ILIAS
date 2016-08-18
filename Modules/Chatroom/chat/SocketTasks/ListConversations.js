var Container = require('../AppContainer');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = this.participant.getConversations();
	var socket = this;

	for(var index in conversations) {
		if(conversations.hasOwnProperty(index)){
			namespace.getDatabase().getLatestMessage(conversations[index], function(row){
				row.userId = row.user_id;
				row.conversationId = row.conversation_id;
				conversations[index].setLatestMessage(row);
			}, function(){
				socket.participant.emit('conversation', conversations[index].json());
			});
		}
	}
};