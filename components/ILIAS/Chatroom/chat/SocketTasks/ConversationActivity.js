var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');

/**
 * @param {string} conversationId
 * @param {number} userId
 * @param {number} timestamp
 */
module.exports = function(conversationId, userId, timestamp) {
	if(conversationId !== null && userId !== null && timestamp !== null) {
		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);

		if (conversation !== null && conversation.isParticipant(this.participant)) {
			namespace.getDatabase().trackActivity(conversationId, userId, timestamp);
			Container.getLogger().debug('Track Activity for user %s in %s: %s', userId, conversationId, timestamp);
		}
	}
};