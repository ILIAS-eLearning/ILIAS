var CONST = require('../Constants');
var Container = require('../AppContainer');


/**
 * @constructor
 */
var RoutingHandler = function RoutingHandler() {

	this.setup = function(app) {
		app.get(_createRoute('/Heartbeat/:namespace'), _getTask('Heartbeat'));
		app.get(_createRoute('/Connect/:namespace/:roomId/:id'), _getTask('SubscribeNamespace'));
		app.get(_createRoute('/CreatePrivateRoom/:namespace/:roomId/:subRoomId/:id/:title'), _getTask('CreateRoom'));
		app.get(_createRoute('/EnterPrivateRoom/:namespace/:roomId/:subRoomId/:id'), _getTask('SubscribeRoom'));
		app.get(_createRoute('/DeletePrivateRoom/:namespace/:roomId/:subRoomId/:id'), _getTask('DeleteRoom'));
		app.get(_createRoute('/LeavePrivateRoom/:namespace/:roomId/:subRoomId/:id'),_getTask('LeaveRoom'));
		app.get(_createRoute('/InvitePrivateRoom/:namespace/:roomId/:subRoomId/:id/:invitedId'), _getTask('InviteRoom'));
		app.get(_createRoute('/ClearMessages/:namespace/:roomId/:subRoomId/:id'),_getTask('ClearMessages'));
		app.get(_createRoute('/Kick/:namespace/:roomId/:subRoomId/:id'), _getTask('Kick'));
		app.get(_createRoute('/Ban/:namespace/:roomId/:subRoomId/:id'), _getTask('Ban'));
		app.get(_createRoute('/GetRooms/:namespace'), _getTask('GetRooms'));
		app.get(_createRoute('/UserConfigChange/:namespace'), _getTask('UserConfigChange'));

		app.get(_createRoute('/Post/:namespace/:roomId'), function onNotSupportedAction(req, res) {
			Container.getLogger().log('silly', 'Not Supported Action %s', 'Post');
			res.send({success: true});
		});
	};

	function _getTask(name) {
		return require('../SystemTasks/'+ name);
	}

	function _createRoute(route) {
		return CONST.API_PREFIX + route;
	}
};

/**
 * @type {RoutingHandler}
 */
module.exports = new RoutingHandler();