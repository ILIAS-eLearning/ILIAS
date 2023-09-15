var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');

/**
 * @param {string} conversationId
 * @param {number} userId
 * @param {number} timestamp
 */
module.exports = function(conversationId, userId) {
	if(conversationId !== null && userId !== null)
	{
		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);

		if(conversation !== null && conversation.isParticipant(this.participant))
		{
			namespace.getDatabase().closeConversation(conversationId, userId);
			Container.getLogger().info('Close Conversation %s for participant %s', conversationId, userId);
		}
	}
};