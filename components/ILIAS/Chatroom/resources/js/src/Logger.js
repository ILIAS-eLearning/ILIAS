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
 *********************************************************************/

export default class Logger {
  logServerResponse(message) {
    this.#log('Server-Response', message);
  };

  logServerRequest(message) {
    this.#log('Server-Request', message);
  }

  logILIASResponse(message) {
    this.#log('ILIAS-Response', message);
  }

  logILIASRequest(message) {
    this.#log('ILIAS-Request', message);
  }

  #log(type, message) {
    console.log(type, message);
  }
}
