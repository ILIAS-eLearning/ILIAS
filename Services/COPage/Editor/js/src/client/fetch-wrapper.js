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

/**
 * fetch wrapper
 *
 */
export default {

  /**
   * @param {string} url
   * @param {Object} data
   * @returns {Promise<Response>}
   */
  postJson: async function (url = '', data = {}) {
    return fetch(url, {
      method: 'POST',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      redirect: 'follow',
      referrerPolicy: 'same-origin',
      body: JSON.stringify(data)
    });
  },

  /**
   * @param {string} url
   * @param {FormData} formData
   * @returns {Promise<Response>}
   */
  postForm: async function (url = '', formData = {}) {
    console.log("POST FORM");
    return fetch(url, {
      method: 'POST',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      redirect: 'follow',
      referrerPolicy: 'same-origin',
      body: formData
    });
  },

  /**
   * @param {string} url
   * @param {Object} params
   * @returns {Promise<Response>}
   */
  getJson: function (url = '', params = {}) {

    let fetch_url = new URL(url);
    let url_params = new URLSearchParams(fetch_url.search.slice(1));
    for (const [key, value] of Object.entries(params)) {
      url_params.append(key, value)
    }
    fetch_url.search = url_params;

    return fetch(fetch_url.href, {
      method: 'GET',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      redirect: 'follow',
      referrerPolicy: 'same-origin'
    });
  }

}