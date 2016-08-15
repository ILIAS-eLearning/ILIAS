var async = require('async');
var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');

/**
 * @param {Namespace} namespace
 * @param {Function} callback
 */
module.exports = function PreloadConversations(namespace, callback) {
	Container.getLogger().info('PreloadConversations for %s', namespace.getName());

	var conversations = namespace.getConversations().all();
	async.eachSeries(conversations, function(conversation, nextLoop) {
		namespace.getDatabase().loadConversationHistory(conversation, function(row){
			var messageObj = {
				conversationId: row.conversationId,
				userId: row.userId,
				message: row.message,
				timestamp: row.timestamp
			};

			conversation.addHistory(messageObj);
		}, nextLoop);
	}, function(err){
		if(err) throw err;

		Container.getLogger().info("Loaded ConversationHistory for namespace %s", namespace.getName());

		callback();
	});





};