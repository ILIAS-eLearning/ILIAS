var Container = require('../AppContainer');

module.exports = function(socket) {

	Container.getLogger().info('New Connection with SocketId: %s', socket.id);

	socket.on('login', _getTask('Login'));
	socket.on('enterRoom', _getTask('EnterRoom'));
	socket.on('message', _getTask('SendMessage'));
	socket.on('disconnect', _getTask('Disconnect'));
};

function _getTask (name) {
	return require('../SocketTasks/'+ name);
}