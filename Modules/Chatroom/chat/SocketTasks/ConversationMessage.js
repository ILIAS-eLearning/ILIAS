var Container = require('../AppContainer');

module.exports = function(conversationId, userId, message) {
	if(conversationId !== null && userId !== null && message !== null)
	{
		Container.getLogger().info('SendMessage "%s" by "%s" in conversation %s', message, userId, conversationId);
		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);
		var messageObj = {
			conversationId: conversationId,
			userId: userId,
			message: message,
			timestamp: (new Date).getTime()
		};

		namespace.getDatabase().persistConversationMessage(messageObj);

		conversation.emit('conversation', conversation.json());
		conversation.send(messageObj);
	}
};