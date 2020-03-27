var CONST	= require('../Constants');
var Container = require('../AppContainer');
var Winston = require('winston');
var Util = require('util');


/**
 * @param {Function} callback
 */
module.exports = function SetupEnvironment(result, callback) {

	var logFile = 'chat.log';
	var errorLogFile = 'chatError.log';
	var serverConfig = Container.getServerConfig();
	var logLevel = "info";

	if (serverConfig.log !== undefined && serverConfig.log !== "") {
		logFile = serverConfig.log;
	}

	if (serverConfig.error_log !== undefined && serverConfig.error_log !== "") {
		errorLogFile = serverConfig.error_log;
	}

	if (
		serverConfig.log_level !== undefined &&
		typeof serverConfig.log_level === "string" &&
		["emerg", "alert", "crit", "error", "warning", "notice", "info", "debug", "silly"].includes(serverConfig.log_level)
	) {
		logLevel = serverConfig.log_level;
	}

	var logger = new (Winston.Logger)({
		transports: [
			new (Winston.transports.File)({
				name: 'log',
				filename: logFile,
				level: logLevel,
				json: false,
				timestamp: function(){
					var date = new Date();
					return date.toDateString() + ' ' + date.toTimeString();
				},
				formatter: function(options) {
					return Util.format(
							'[%s] %s - %s %s',
							options.timestamp(),
							options.level.toUpperCase(),
							options.message,
							(options.meta !== undefined && options.meta.length > 0)? '\n\t' + JSON.stringify(options.meta) : '');
				}
			})
		]
	});

	Winston.handleExceptions(
		new (Winston.transports.File)({
			name: 'errorlog',
			filename: errorLogFile,
			handleExceptions: true,
			humanReadableUnhandledException: true,
			json: false,
			timestamp: function(){
				var date = new Date();
				return date.toDateString() + ' ' + date.toTimeString();
			},
			formatter: function(options) {
				return Util.format(
					'[%s] %s - %s \n %s',
					options.timestamp(),
					options.level.toUpperCase(),
					JSON.stringify(options.meta.process),
					options.meta.stack.join('\n')
				)
			}
		})
	);

	logger.exitOnError = false;
	logger.info('Starting Server!');
	logger.info("Log Level: " + logLevel);

	Container.setLogger(logger);

	callback(null);
};