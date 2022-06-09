/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import Response from './response.js';

/**
 * Response factory
 */
export default class ResponseFactory {

  /**
   */
  constructor() {
  }

  /**
   * @param {Action} action
   * @param {Object} payload
   * @returns {Response}
   */
  response(action, payload) {
    console.log("...got payload");
    console.log(payload);
    return new Response(action, payload);
  }
}