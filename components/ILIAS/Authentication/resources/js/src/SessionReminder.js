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

export default class SessionReminder {
  /**
   * @type {Object}
   */
  #options;

  /**
   * @type {State}
   */
  #state;

  /**
   * @type {Logger}
   */
  #logger;

  /**
   * @type {PeriodicalExecuter}
   */
  #executer;

  /**
   * @type {typeof PeriodicalExecuter}
   */
  #periodicalExecuter;

  /**
   * @type {Window}
   */
  #windowObj;

  /**
   * @param {Object} options - Configuration options.
   * @param {State} state - The state.
   * @param {Logger} logger - The logger.
   * @param {typeof PeriodicalExecuter} PeriodicalExecuter - Class for periodic execution.
   * @param {Window} windowObj - The global window object.
   */
  constructor(
    options,
    state,
    logger,
    PeriodicalExecuter,
    windowObj,
  ) {
    this.#options = options;
    this.#state = state;
    this.#logger = logger;
    this.#periodicalExecuter = PeriodicalExecuter;
    this.#windowObj = windowObj;
  }

  run() {
    this.#windowObj.addEventListener('beforeunload', () => {
      this.#state.update({ status: 'unlocked' });
      this.#logger.info("Unlocked session reminder on browser's unload event");
    });

    this.#logger.info('Session reminder started');

    if (this.#state.get().hash !== this.#options.hash) {
      this.#state.update({ activation: 'enabled', status: 'unlocked', hash: this.#options.hash });
      this.#logger.info(
        'Session cookie changed after new login or session reminder initially started '
        + 'for current session: Released lock and enabled reminder.',
      );
    }

    this.#executer = new this.#periodicalExecuter(
      this.#windowObj,
      this.#callback.bind(this),
      this.#options.frequency * 1000,
    );
    this.#logger.info('Started periodical executer');
  }

  #callback() {
    const state = this.#state.get();
    if (state.activation === 'disabled' || state.status === 'locked') {
      this.#logger.info('Session reminder disabled or locked for current user session');
      return;
    }

    this.#state.update({ status: 'locked' });
    this.#logger.info('Session reminder locked');
    this.#executer.stop();
    this.#logger.info('Stopped periodical executer');

    const formData = new FormData();
    formData.append('hash', this.#options.hash);

    fetch(this.#options.url, {
      method: 'POST',
      body: formData,
      credentials: 'omit',
    })
      .then((response) => response.json())
      .then((data) => {
        try {
          if (typeof data !== 'object'
            || data === null
            || Array.isArray(data)
            || JSON.stringify(data) === undefined) {
            throw new Error('The response body seems not to be valid JSON');
          }
        } catch (e) {
          throw new Error('Invalid response format', { cause: e });
        }

        if (data.message && typeof data.message === 'string') {
          this.#logger.info(data.message);
        }

        if (!data.remind) {
          this.#logger.info('Reminder of session expiration not necessary: Session reminder unlocked');
          return;
        }

        const extend = this.#windowObj.confirm(data.txt);
        if (!extend) {
          this.#state.update({ activation: 'disabled', status: 'unlocked' });
          this.#logger.info('User disabled reminder for current session: Session reminder disabled but unlocked');
          return;
        }

        fetch(data.extend_url, { method: 'GET' })
          .then(() => {
            this.#logger.info('Session extended successfully');
            return new Promise(
              (resolve) => {
                resolve(true);
              },
            );
          })
          .catch((error) => {
            this.#logger.error('Fetch error occurred:', error);
          });
      })
      .catch((error) => {
        this.#logger.error('Fetch error occurred:', error);
      })
      .finally(() => {
        this.#state.update({ status: 'unlocked' });
        this.#logger.info('Unlocked session reminder');
        this.#executer = new this.#periodicalExecuter(
          this.#windowObj,
          this.#callback.bind(this),
          this.#options.frequency * 1000,
        );
        this.#logger.info('Restarted periodical executer');
      });
  }
}
