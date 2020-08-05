/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    // Default options are marked with *
    const response = await fetch(url, {
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
    return response.json(); // parses JSON response into native JavaScript objects
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