var Container = require('../AppContainer');

module.exports = function(conversationId, oldestMessageTimestamp) {
	if(conversationId !== null)
	{
		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);
		var history = [];
		var oldestTimestamp = oldestMessageTimestamp;
		var socket = this;

		if(conversation !== null && conversation.isParticipant(this.participant))
		{
			function onConversationResult(row){
				if(oldestTimestamp === null || oldestTimestamp > row.timestamp) {
					oldestTimestamp = row.timestamp;
				}
				row.userId = row.user_id;
				row.conversationId = row.conversation_id;
				history.push(row);
			}

			function emitConversationHistory(err){
				if(err) {
					throw err;
				}

				var json = conversation.json();
				json.messages = history;
				json.oldestMessageTimestamp = oldestTimestamp;

				socket.participant.emit('history', json);

				Container.getLogger().info('Requested History for %s since %s', conversationId, oldestMessageTimestamp);
			}

			namespace.getDatabase().loadConversationHistory(
				conversation.getId(),
				oldestMessageTimestamp,
				onConversationResult,
				emitConversationHistory
			);
		}
	}

};