var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');

/**
 * @param {string} conversationId
 * @param {number} userId
 * @param {number} timestamp
 */
module.exports = function(conversationId, userId, timestamp) {
	Container.getLogger().info('Track Activity for user %s in %s: %s', userId, conversationId, timestamp);
	var namespace = Container.getNamespace(this.nsp.name);
	namespace.getDatabase().trackActivity(conversationId, userId, timestamp);
};