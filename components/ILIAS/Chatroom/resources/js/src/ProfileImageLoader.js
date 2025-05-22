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

const DEBOUNCE_TIMOUT = 20;

export default class ProfileImageLoader {
  /** @type {string} */
  #url;
  /** @type {string} */
  #fallbackImage;
  /** @type {object.<string, string>} */
  #loaded;
  /** @type {object.<string, {value: object, waiting: Array.<{resolve: function, reject: function}>}} */
  #queue;
  /** @type {function(): void} */
  #reset;

  /**
   * @param {string} url
   * @param {string} fallbackImage
   */
  constructor(url, fallbackImage) {
    this.#url = url;
    this.#fallbackImage = fallbackImage;
    this.#loaded = {};
    this.#queue = {};
    this.#reset = () => {};
  }

  /**
   * @param {object} user
   * @returns {Promise.<string>}
   */
  imageOfUser(user) {
    if (this.#loaded[this.#key(user)]) {
      return Promise.resolve(this.#loaded[this.#key(user)]);
    }
    return this.#debounceLoad(user);
  }

  /**
   * @param {object[]} users
   * @returns {Promise.<string[]>}
   */
  imagesOfUsers(users) {
    return Promise.all(users.map(this.imageOfUser.bind(this)));
  }

  defaultImage() {
    return this.#fallbackImage;
  }

  /**
   * @param {object} user
   */
  #debounceLoad(user) {
    return new Promise((resolve, reject) => {
      const key = this.#key(user);
      this.#queue[key] = this.#queue[key] || {value: user, waiting: []};
      this.#queue[key].waiting.push({resolve, reject});
      this.#reset();
      this.#reset = clearTimeout.bind(null, setTimeout(this.#request.bind(this), DEBOUNCE_TIMOUT));
    });
  }

  #request() {
    const profiles = Object.values(this.#queue).map(({value}) => value);
    const loadImage = fetch(this.#url, {
      method: 'POST',
      body: JSON.stringify({profiles}),
      headers: {'Content-Type': 'application/json'}
    }).then(r => r.json());

    loadImage.then(response => Object.entries(this.#flushQueue()).forEach(
      ([key, {waiting}]) => waiting.forEach(
        ({resolve, reject}) => {
          if (response[key]) {
            this.#loaded[key] = response[key];
            resolve(response[key]);
          } else {
            reject('Image not returned from server.');
          }
        }
      )
    ));

    loadImage.catch(
      error => Object.values(this.#flushQueue())
        .flatMap(v => v.waiting)
        .forEach(p => p.reject(error))
    );
  }

  /**
   * @param {object} user
   * @returns {string}
   */
  #key(user) {
    return JSON.stringify(user);
  }

  #flushQueue() {
    const queue = this.#queue;
    this.#queue = {};
    return queue;
  }
}
