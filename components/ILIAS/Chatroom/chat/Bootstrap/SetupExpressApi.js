var Express = require('express');
var RoutingHandler = require('../Handler/RoutingHandler');
var Container = require('../AppContainer');
var Authentication = require('../Handler/AuthenticationHandler');
var CONST = require('../Constants');

/**
 * @param {Function} callback
 */
module.exports = function SetupExpressApi(callback) {
	var api = Express();

	api.all(CONST.API_PREFIX+'/:action/:namespace/*', Authentication);

	RoutingHandler.setup(api);

	Container.setApi(api);

	callback(null);
};