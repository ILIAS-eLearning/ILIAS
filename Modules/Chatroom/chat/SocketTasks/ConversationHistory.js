var Container = require('../AppContainer');

module.exports = function(conversationId) {
	Container.getLogger().info('Requested History');

	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);

	var history = [];
	var socket = this;
	var newMessages = false;
	namespace.getDatabase().loadConversationHistory(conversation, function(row){
		newMessages = true;
		conversation.addHistory(row);
		history.push(row);
	}, function(err){
		if(err) throw err;

		var json = conversation.json();

		if(!newMessages) {
			json.messages = {};
		}
		socket.participant.emit('history', json);
	});
};