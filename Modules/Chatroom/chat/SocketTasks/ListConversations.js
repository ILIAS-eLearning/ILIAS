var Container = require('../AppContainer');

module.exports = function() {
	Container.getLogger().info('Requested Conversations list');

	var conversations = this.participant.getConversations();

	for(var index in conversations) {
		if(conversations.hasOwnProperty(index)){
			this.participant.emit('conversation', conversations[index].json());
		}
	}
};