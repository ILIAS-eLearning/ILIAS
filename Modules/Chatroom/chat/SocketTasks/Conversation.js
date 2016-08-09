var Container = require('../AppContainer');
var UUID	  = require('node-uuid');
var Conversation = require('../Model/Conversation');

	/**
 * @param {Array} participants
 */
module.exports = function(participants) {
	Container.getLogger().info('Conversation Requested');
	var namespace = Container.getNamespace(this.nsp.name);
	var conversations = namespace.getConversations();
	var conversation = conversations.getForParticipants(participants);

	if(conversation == null) {
		Container.getLogger().info('No Conversation found. Creating new one');
		conversation = new Conversation(UUID.v4());
		conversations.add(conversation);
	}
	for(var key in participants) {
		var participant = namespace.getSubscriber(participants[key].id);
		conversation.addParticipant(participants[key].id);

		if (participant !== null) {
			participant.join(conversation.id);
		}
	}

	console.log(conversation.getId());
	console.log(conversation.getParticipants());

	this.emit('conversation', conversation.getId());
};