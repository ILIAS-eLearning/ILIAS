var ClearAction = require('../Model/Messages/ClearAction');
var Notice = require('../Model/Messages/Notice');
var Container = require('../AppContainer');

module.exports = function(req, res)
{
	var roomId = parseInt(req.params.roomId);
	var subRoomId = parseInt(req.params.subRoomId);
	var serverRoomId = roomId + '_' + subRoomId;
	var namespace = Container.getNamespace(req.params.namespace);

	Container.getLogger().info('Clear messages in room %s of namespace %s', serverRoomId, namespace.getName());

	var notice = Notice.create('history_has_been_cleared', roomId, subRoomId);
	var action = ClearAction.create(roomId, subRoomId);

	namespace.getIO().to(serverRoomId).emit('clear', action);
	namespace.getIO().to(serverRoomId).emit('notice', notice);

	res.send({success: true});
};
