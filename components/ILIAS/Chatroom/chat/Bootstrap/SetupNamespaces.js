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

var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var SetupDatabase = require('./SetupDatabase');
var PreloadData = require('./PreloadData');
const sync = require('../Helper/sync');

module.exports = function SetupNamespaces(result, callback) {

	var clientConfigs = Container.getClientConfigs();

	function setupNamespace(config, nextLoop) {
		var namespace = Handler.createNamespace(config.name);

		Container.getLogger().info('SetupNamespace %s!', namespace.getName());

		const p = sync.toPromise(SetupDatabase)(namespace, config)
		      .then(sync.toPromise(PreloadData));

		sync.fromPromise(p, nextLoop);
	}

	const p = sync.each(clientConfigs, setupNamespace)
	      .then(() => Container.getLogger().info('SetupNamespace finished!'));

	sync.fromPromise(p, callback);
};
