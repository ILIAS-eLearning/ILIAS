const Container = require('../AppContainer');

/**
 * @param {string} conversationId
 * @param {number} userId
 */
module.exports = function (conversationId, userId) {
	if (conversationId != null && userId != null) {
		const namespace = Container.getNamespace(this.nsp.name),
			conversation = namespace.getConversations().getById(conversationId);

		if (conversation !== null && this.participant && conversation.isParticipant(this.participant)) {
			namespace.getDatabase().closeConversation(conversationId, userId);
			Container.getLogger().info('Close Conversation %s for participant %s', conversationId, userId);
		}
	}
};