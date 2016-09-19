
var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');
var Participant = require('../Model/ConversationParticipant');

/**
 * @param {Namespace} namespace
 * @param {Function} callback
 */
module.exports = function PreloadConversations(namespace, callback) {
	Container.getLogger().info('PreloadConversations for %s', namespace.getName());

	namespace.getDatabase().loadConversations(function(row) {
		var participants = JSON.parse(row.participants);

		var conversation = new Conversation(row.id);
		conversation.setIsGroup(row.is_group);

		for(var index in participants) {
			if(participants.hasOwnProperty(index)){
				var participant = namespace.getSubscriberWithOfflines(participants[index].id, participants[index].name);
				participant.setOnline(false);
				participant.join(conversation.getId());

				conversation.addParticipant(participant);
				namespace.addSubscriber(participant);
			}
		}
		namespace.getConversations().add(conversation);

	}, function() {
		callback(null, namespace);
	});
};