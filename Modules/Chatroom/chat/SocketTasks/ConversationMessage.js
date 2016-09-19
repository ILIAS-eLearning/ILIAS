var Container  = require('../AppContainer');
var HTMLEscape = require('../Helper/HTMLEscape');

module.exports = function(conversationId, userId, message) {
	if (conversationId !== null && userId !== null && message !== null) {

		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);
		var participant = namespace.getSubscriber(userId);

		if(conversation.isParticipant(participant))
		{
			var messageObj = {
				conversationId: conversationId,
				userId: userId,
				message: HTMLEscape.escape(message),
				timestamp: (new Date).getTime()
			};

			namespace.getDatabase().persistConversationMessage(messageObj);

			conversation.emit('conversation', conversation.json());
			conversation.send(messageObj);

			Container.getLogger().info('SendMessage by "%s" in conversation %s', userId, conversationId);
		}
	}
};