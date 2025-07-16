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

var ReadCommandArguments = require('./ReadCommandArguments');
var ReadServerConfig = require('./ReadServerConfig');
var ReadClientConfigs = require('./ReadClientConfigs');
var SetupEnvironment = require('./SetupEnvironment');
var SetupExpressApi = require('./SetupExpressApi');
var SetupNamespaces = require('./SetupNamespaces');
var SetupIM = require('./SetupIM');
var SetupExitHandler = require('./SetupExitHandler');
var SetupServer = require('./SetupServer');
var SetupClearMessagesProcess = require('./SetupClearMessagesProcess');
var UserSettingsProcess = require('./UserSettingsProcess');
var Container = require('../AppContainer');
const sync = require('../Helper/sync');

var Bootstrap = function Bootstrap() {
	this.boot = function() {
		function onBootCompleted(err, result){
			Container.getServer().listen(Container.getServerConfig().port, Container.getServerConfig().address);
			Container.getLogger().info("The Server is Ready to use! Listening on: %s://%s:%s", Container.getServerConfig().protocol, Container.getServerConfig().address, Container.getServerConfig().port);
		}

		const p = {};
		const then = (p, proc) => p.then(sync.toPromise(proc));

		p.readCommandArguments = sync.toPromise(ReadCommandArguments)();
		p.setupExpressApi = sync.toPromise(SetupExpressApi)(),
		p.readServerConfig = then(p.readCommandArguments, ReadServerConfig);
		p.readClientConfigs = then(p.readCommandArguments, ReadClientConfigs);
		p.setupEnvironment = then(Promise.all([p.readCommandArguments, p.readServerConfig]), SetupEnvironment);
		p.setupNamespaces = then(p.readClientConfigs, SetupNamespaces);
		p.setupIM = then(p.setupNamespaces, SetupIM);
		p.setupExitHandler = then(p.setupNamespaces, SetupExitHandler);
		p.setupServer = then(Promise.all([p.setupNamespaces, p.setupIM]), SetupServer);
		p.setupClearProcess = then(p.setupServer, SetupClearMessagesProcess);
		p.setupUserSettingsProcess = then(p.setupServer, UserSettingsProcess);

		Promise.all(Object.values(p)).then(onBootCompleted);
	};
};

module.exports = new Bootstrap();
