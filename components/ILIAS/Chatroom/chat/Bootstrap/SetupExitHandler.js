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
const sync = require('../Helper/sync');

/**
 *
 */
module.exports = function SetupExitHandler(result, callback) {

	var CONST_NO_CLEANUP_CODE = 99;

	process.on('cleanup',  function onCleanUp(callback) {
		_cleanUp(callback);
	});

	process.on("exit", function onExit(code) {
		if (code !== CONST_NO_CLEANUP_CODE) {
			process.emit('cleanup');
		}
	});

	process.on("SIGINT", function onSignalInterrupt() {
		process.emit('cleanup', function onSignalInterruptCleanup() {
			process.exit(CONST_NO_CLEANUP_CODE);
		});
	});

	process.on("SIGTERM", function onSignalTermination() {
		process.emit('cleanup', function onSignalTerminationCleanup() {
			process.exit(CONST_NO_CLEANUP_CODE);
		});
	});

	_cleanUp(callback);
};

function _cleanUp(callback)
{
	//process.stdin.resume(); //so the program will not close instantly
	var namespaces = Container.getNamespaces();

	function disconnectSocketsAndUsers(namespace, nextLoop){
		Container.getLogger().info('Cleanup %s', namespace.getName());
		namespace.disconnectSockets();
		namespace.getDatabase().disconnectAllUsers(nextLoop);
	}

	sync.fromPromise(sync.each(namespaces, disconnectSocketsAndUsers), callback);
}
