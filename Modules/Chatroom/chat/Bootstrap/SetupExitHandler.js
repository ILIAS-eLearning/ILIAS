var Container = require('../AppContainer');
var async = require('async');

/**
 *
 */
module.exports = function SetupExitHandler(callback)
{
	var CONST_NO_CLEANUP_CODE = 99;

	var cleanUp = function(callback){
		_cleanUp(callback);
	};

	process.on('cleanup', cleanUp);

	var emitCleanUp = function(code){
		if(code != CONST_NO_CLEANUP_CODE)
		{
			process.emit('cleanup');
		}
	};

	process.on("exit", emitCleanUp);

	var signalInterrupt = function(){
		var cleaunup = function(){
			process.exit(CONST_NO_CLEANUP_CODE);
		};
		process.emit('cleanup', cleaunup);
	};

	process.on("SIGINT", signalInterrupt);

	var signalTermination = function(){
		var cleanup = function() {
			process.exit(CONST_NO_CLEANUP_CODE);
		};
		process.emit('cleanup', cleanup);
	};

	process.on("SIGTERM", signalTermination);

	_cleanUp(callback);
};

function _cleanUp(callback)
{
	//process.stdin.resume(); //so the program will not close instantly
	var namespaces = Container.getNamespaces();

	var cleanup = function(namespace, nextLoop){
		Container.getLogger().info('Cleanup %s', namespace.getName());
		namespace.disconnectSockets();
		namespace.getDatabase().disconnectAllUsers(nextLoop);
	};

	var onEnd = function(err){
		if(err) {
			throw err;
		}

		callback();
	};

	async.eachSeries(namespaces, cleanup, onEnd);
}
