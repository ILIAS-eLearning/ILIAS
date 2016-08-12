var Container = require('../AppContainer');

module.exports = function(conversationId, userId, message) {
	Container.getLogger().info('SendMessage "%s" by "%s" in conversation %s', message, userId, conversationId);

	var conversation = Container.getNamespace(this.nsp.name).getConversations().getById(conversationId);

	conversation.emit('conversation', conversation.json());
	conversation.send({
		conversationId: conversationId,
		userId: userId,
		message: message,
		timestamp: (new Date).getTime()
	});
};