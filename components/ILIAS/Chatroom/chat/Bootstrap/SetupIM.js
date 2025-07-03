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
const sync = require('../Helper/sync');
var PreloadConversations = require('./PreloadConversations');

module.exports = function SetupIM(result, callback) {

	function setupIMNamespace(namespace, callback) {
		var namespaceIM = Handler.createNamespace(namespace.getName() + '-im');
		namespaceIM.setIsIM(true);

		Container.getLogger().info('SetupNamespace IM: %s!', namespaceIM.getName());

		namespaceIM.setDatabase(namespace.getDatabase());

		PreloadConversations(namespaceIM, callback);
	}

	const p = sync.each(Container.getNamespaces(), setupIMNamespace)
	      .then(() => Container.getLogger().info('SetupNamespace IM finished!'));

	sync.fromPromise(p, callback);
};
