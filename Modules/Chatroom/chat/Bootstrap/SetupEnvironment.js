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

    if(serverConfig.log !== undefined && serverConfig.log !== "")
    {
        logFile = serverConfig.log;
    }
    if(serverConfig.error_log !== undefined && serverConfig.error_log !== "")
    {
        errorLogFile = serverConfig.error_log;
    }

    var logger = new (Winston.Logger)({
        transports: [
            new (Winston.transports.File)({
                name: 'log',
                filename: logFile,
                level: 'info',
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

    Container.setLogger(logger);

    callback(null);
};