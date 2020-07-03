/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Response
 */
export default class Response {

  /**
   * @type {Action}
   */
  action;

  /**
   * @type {Object}
   */
  payload;

  /**
   * @param {Action} action
   * @param {Object} payload
   */
  constructor(action, payload = {}) {
    this.action = action;
    this.payload = payload;
  }

  /**
   * @returns {Action}
   */
  getAction() {
    return this.action;
  }

  /**
   * @returns {Object}
   */
  getPayload() {
    return this.payload;
  }
}