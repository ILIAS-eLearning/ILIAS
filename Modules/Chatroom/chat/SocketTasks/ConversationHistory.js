const Container = require('../AppContainer');

/**
 *
 * @param {string} conversationId
 * @param {number|null} oldestMessageTimestamp
 * @param {boolean}  reverseSorting
 */
module.exports = function (conversationId, oldestMessageTimestamp, reverseSorting) {
	if (conversationId != null) {
		const namespace = Container.getNamespace(this.nsp.name),
			conversation = namespace.getConversations().getById(conversationId),
			history = [],
			socket = this;

		let oldestTimestamp = oldestMessageTimestamp;

		if (conversation !== null && this.participant && conversation.isParticipant(this.participant)) {
			function onConversationResult(row) {
				if (null == oldestTimestamp || oldestTimestamp > row.timestamp) {
					oldestTimestamp = row.timestamp;
				}
				row.userId = row.user_id;
				row.conversationId = row.conversation_id;
				history.push(row);
			}

			function emitConversationHistory(err) {
				if (err) {
					throw err;
				}

				const json = conversation.json();
				json.messages = (reverseSorting ? history.reverse() : history);
				json.reverseSorting = reverseSorting;
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