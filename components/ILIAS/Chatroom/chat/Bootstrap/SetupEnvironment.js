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

const Container = require('../AppContainer');
const { createLogger, transports, format } = require('winston');
const DateHelper = require('../Helper/Date');

const timestamp = () => {
  const t = new Date();
  return DateHelper.iso8601DatetimeFormat(t) + DateHelper.iso8601TimezoneFormat(t);
};

const LOG_LEVELS = ['emerg', 'alert', 'crit', 'error', 'warning', 'notice', 'info', 'debug', 'silly'];

/**
 * @param {Function} callback
 */
module.exports = function SetupEnvironment(result, callback) {
  const serverConfig = Container.getServerConfig();
  const logLevel = LOG_LEVELS.includes(serverConfig.log_level) ? serverConfig.log_level : 'info';

  const logger = createLogger({
    exitOnError: false,
    transports: [
      new transports.File({
	name: 'log',
	filename: serverConfig.log || 'chat.log',
	level: logLevel,
        format: format.combine(
          format.splat(),
          format.printf(({level, message, ...meta}) => (
            `[${timestamp()}] ${level.toUpperCase()} - ${message} ${Object.keys(meta).length === 0 ? '' : `\n\t${JSON.stringify(meta)}`}`
          ))
        ),
      }),
      new transports.File({
	name: 'errorlog',
        level: 'error',
	filename: serverConfig.error_log || 'chatError.log',
	handleExceptions: true,
        format: format.printf(({level, process, stack}) => (
          `[${timestamp()}] ${level.toUpperCase()} - ${JSON.stringify(process)} \n ${stack}`
        )),
      })
    ],
  });

  logger.info('Starting Server!');
  logger.info('Log Level: ' + logLevel);

  Container.setLogger(logger);

  callback(null);
};
