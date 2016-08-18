var Container = require('../AppContainer');

module.exports = function(conversationId, oldestMessageTimestamp) {
	Container.getLogger().info('Requested History for %s since %s', conversationId, oldestMessageTimestamp);

	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);
	var history = [];
	var oldestTimestamp = oldestMessageTimestamp;
	var socket = this;

	namespace.getDatabase().loadConversationHistory(conversation.getId(), oldestMessageTimestamp, function(row){
		if(oldestTimestamp == null || oldestTimestamp > row.timestamp) {
			oldestTimestamp = row.timestamp;
		}
		history.push(row);
	}, function(err){
		if(err) throw err;

		var json = conversation.json();
		json.messages = history;
		json.oldestMessageTimestamp = oldestTimestamp;

		socket.participant.emit('history', json);
	});
};