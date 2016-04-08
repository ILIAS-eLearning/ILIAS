var Container = require('../AppContainer');
var async = require('async');

/**
 *
 */
module.exports = function SetupExitHandler(callback)
{
	var CONST_NO_CLEANUP_CODE = 99;

	process.on('cleanup', function(callback){
		_cleanUp(callback)
	});

	process.on("exit", function(code){
		if(code != CONST_NO_CLEANUP_CODE)
		{
			process.emit('cleanup');
		}
	});

	process.on("SIGINT", function(){
		process.emit('cleanup', function(){
			process.exit(CONST_NO_CLEANUP_CODE);
		});
	});

	process.on("SIGTERM", function(){
		process.emit('cleanup', function() {
			process.exit(CONST_NO_CLEANUP_CODE);
		});
	});

	_cleanUp(callback);
};

function _cleanUp(callback)
{
	//process.stdin.resume(); //so the program will not close instantly
	var namespaces = Container.getNamespaces();

	async.eachSeries(namespaces, function(namespace, nextLoop){
		Container.getLogger().info('Cleanup %s', namespace.getName());
		namespace.disconnectSockets();
		namespace.getDatabase().disconnectAllUsers(nextLoop);

	},
	function(err){
		if(err) throw err;

		callback();
	});
}