const Container = require('../AppContainer');

/**
 * @param {string} conversationId
 * @param {number} userId
 * @param {number} timestamp
 */
module.exports = function (conversationId, userId, timestamp) {
	if (conversationId != null && userId != null && timestamp != null) {
		const namespace = Container.getNamespace(this.nsp.name),
			conversation = namespace.getConversations().getById(conversationId);

		if (conversation !== null && this.participant && conversation.isParticipant(this.participant)) {
			namespace.getDatabase().trackActivity(conversationId, userId, timestamp);
			Container.getLogger().info('Track Activity for user %s in %s: %s', userId, conversationId, timestamp);
		}
	}
};