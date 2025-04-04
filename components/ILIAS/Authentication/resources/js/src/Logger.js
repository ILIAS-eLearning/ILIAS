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
 *
 ******************************************************************** */

/**
 * @typedef {Object} LogLevel
 * @property {number} value
 * @property {string} name
 */

export default class Logger {
  /**
   * @param {number} value - The code of the log level
   * @param {string} name - The readable log level name
   * @returns {LogLevel}
   */
  static defineLogLevel(value, name) {
    return { value, name };
  }

  static TRACE = Logger.defineLogLevel(50, 'TRACE');

  static DEBUG = Logger.defineLogLevel(100, 'DEBUG');

  static INFO = Logger.defineLogLevel(200, 'INFO');

  static NOTICE = Logger.defineLogLevel(250, 'NOTICE');

  static WARNING = Logger.defineLogLevel(300, 'WARN');

  static ERROR = Logger.defineLogLevel(400, 'ERROR');

  static CRITICAL = Logger.defineLogLevel(500, 'CRITICAL');

  static ALERT = Logger.defineLogLevel(550, 'ALERT');

  static EMERGENCY = Logger.defineLogLevel(600, 'EMERGENCY');

  static OFF = Logger.defineLogLevel(1000, 'OFF');

  static LEVELS = [
    Logger.TRACE, Logger.DEBUG, Logger.INFO, Logger.NOTICE,
    Logger.WARNING, Logger.ERROR, Logger.CRITICAL, Logger.ALERT,
    Logger.EMERGENCY, Logger.OFF,
  ];

  /**
   * @type {Console}
   */
  #consoleObj;

  /**
   * @type {LogLevel}
   */
  #level;

  /**
   * @param {Console} consoleObj - The console object for logging.
   * @param {LogLevel} level - The logging level.
   */
  constructor(consoleObj, level = Logger.DEBUG) {
    this.#consoleObj = consoleObj;
    this.setLevel(level);
  }

  /**
   * @param {LogLevel} level
   */
  setLevel(level) {
    if (level && 'value' in level) {
      this.#level = level;
    }
  }

  /**
   * @returns {LogLevel}
   */
  getLevel() {
    return this.#level;
  }

  /**
   * @param {LogLevel} level
   * @returns {boolean}
   */
  enabledFor(level) {
    return level.value >= this.#level.value;
  }

  /**
   *
   * @param {LogLevel} level
   * @param {...*} args
   */
  logMessage(level, args) {
    if (!this.enabledFor(level)) {
      return;
    }

    let argumentList = args;

    let [firstElement, ...rest] = argumentList;
    if (typeof firstElement === 'string') {
      firstElement = `SessionReminder | ${firstElement}`;
      argumentList = [firstElement, ...rest];
    }

    if (level.name.toLowerCase() in this.#consoleObj) {
      this.#consoleObj[level.name.toLowerCase()](...argumentList);
    } else {
      this.#consoleObj.error(...argumentList);
    }
  }

  /**
   * @param {...*} args
   */
  trace(...args) { this.logMessage(Logger.TRACE, args); }

  /**
   * @param {...*} args
   */
  debug(...args) { this.logMessage(Logger.DEBUG, args); }

  /**
   * @param {...*} args
   */
  info(...args) { this.logMessage(Logger.INFO, args); }

  /**
   * @param {...*} args
   */
  notice(...args) { this.logMessage(Logger.NOTICE, args); }

  /**
   * @param {...*} args
   */
  warn(...args) { this.logMessage(Logger.WARNING, args); }

  /**
   * @param {...*} args
   */
  error(...args) { this.logMessage(Logger.ERROR, args); }

  /**
   * @param {...*} args
   */
  critical(...args) { this.logMessage(Logger.CRITICAL, args); }

  /**
   * @param {...*} args
   */
  alert(...args) { this.logMessage(Logger.ALERT, args); }

  /**
   * @param {...*} args
   */
  emergency(...args) { this.logMessage(Logger.EMERGENCY, args); }

  /**
   * @param {...*} args
   */
  log(...args) { this.info(...args); }

  /**
   * @param {number} numericLevel - The log level to be used (e.g. provided from backend config)
   * @returns {LogLevel}
   */
  static levelForNumericValue(numericLevel) {
    if (Number.isNaN(numericLevel)) {
      return Logger.DEBUG;
    }

    for (const level of Logger.LEVELS) {
      if (Number.parseInt(numericLevel, 10) === level.value) {
        return level;
      }
    }

    return Logger.DEBUG;
  }
}
