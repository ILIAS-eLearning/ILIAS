var Container = require('../AppContainer');
var async = require('async');

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

	function onCleanUpFinished(err){
		if(err) {
			throw err;
		}

		callback();
	}

	async.eachSeries(namespaces, disconnectSocketsAndUsers, onCleanUpFinished);
}
