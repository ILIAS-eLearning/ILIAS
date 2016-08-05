var Container = require('../AppContainer');

module.exports = function(socket) {

	Container.getLogger().info('New IM Connection with SocketId: %s', socket.id);


	socket.on('login', _getTask('ConversationLogin'));
	socket.on('conversations', _getTask('ListConversations'));
	socket.on('conversation', _getTask('Conversation'));
	socket.on('addParticipant', _getTask('ConversationAddUser'));
	socket.on('message', _getTask('ConversationMessage'));
	socket.on('history', _getTask('ConversationHistory'));
};

var _getTask = function(name) {
	return require('../SocketTasks/'+ name);
};