var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');

/**
 * @param {string} conversationId
 * @param {number} userId
 * @param {number} timestamp
 */
module.exports = function(conversationId, userId) {
	Container.getLogger().info('Close Conversation %s for participant %s', conversationId, userId);
	var namespace = Container.getNamespace(this.nsp.name);
	namespace.getDatabase().closeConversation(conversationId, userId);
};