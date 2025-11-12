/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

const Express = require('express');
const RoutingHandler = require('../Handler/RoutingHandler');
const Container = require('../AppContainer');
const Authentication = require('../Handler/AuthenticationHandler');
const CONST = require('../Constants');

/**
 * @param {Function} callback
 */
module.exports = function SetupExpressApi(callback) {
	var api = Express();

	api.all(CONST.API_PREFIX + '/:action/:namespace/*splat', Authentication);

	RoutingHandler.setup(api);

	Container.setApi(api);

	callback(null);
};
