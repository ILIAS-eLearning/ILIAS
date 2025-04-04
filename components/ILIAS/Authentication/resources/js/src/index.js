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

import il from 'il';
import State from './State.js';
import Storage from './Storage.js';
import PeriodicalExecuter from './PeriodicalExecuter.js';
import SessionReminder from './SessionReminder.js';
import Logger from './Logger.js';

il.SessionReminder = il.SessionReminder || (() => {
  let instance = null;

  return {
    init(serverOptions, windowObj, consoleObj) {
      if (instance) {
        instance.logger?.warning('SessionReminder init() called again; already running.');
        return instance;
      }

      const defaultOptions = {
        url: '',
        clientId: '',
        hash: '',
        frequency: 60,
        logLevel: Logger.INFO.value,
      };

      const options = Object.fromEntries(
        Object.entries({ ...defaultOptions, ...serverOptions })
          .filter(([key]) => key in defaultOptions),
      );

      const logger = new Logger(
        consoleObj,
        Logger.levelForNumericValue(options.logLevel),
      );

      if (!('localStorage' in windowObj)) {
        logger.warn("No 'localStorage' support.");
      }

      const ls = 'localStorage' in windowObj ? windowObj.localStorage : (() => {
        const items = {};
        return {
          removeItem: (key) => { delete items[key]; },
          getItem: (key) => items[key] ?? null,
          setItem: (key, value) => { items[key] = value; },
        };
      })();

      const storage = new Storage(
        windowObj,
        ls,
        logger,
        'il_sr',
        options.clientId,
      );

      const state = new State(
        windowObj,
        storage,
        logger,
        {
          activation: 'disabled',
          status: 'unlocked',
          hash: '',
        },
        'state',
      );

      instance = new SessionReminder(
        options,
        state,
        logger,
        PeriodicalExecuter,
        windowObj,
      );

      return instance;
    },

    run() {
      if (!instance) {
        throw new Error('SessionReminder not initialized. Call init() first.');
      }

      instance.run();

      this.run = () => {
        instance.logger?.warning('SessionReminder run() called again; already running.');
      };
    },
  };
})();
