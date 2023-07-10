var ClearAction = require('../Model/Messages/ClearAction');
var Notice = require('../Model/Messages/Notice');
var Container = require('../AppContainer');

module.exports = function(req, res)
{
	var roomId = parseInt(req.params.roomId);
	var serverRoomId = roomId + '_0';
	var namespace = Container.getNamespace(req.params.namespace);

	Container.getLogger().info('Clear messages in room %s of namespace %s', serverRoomId, namespace.getName());

	var notice = Notice.create('history_has_been_cleared', roomId);
	var action = ClearAction.create(roomId);

	namespace.getIO().to(serverRoomId).emit('clear', action);
	namespace.getIO().to(serverRoomId).emit('notice', notice);

	res.send({success: true});
};
