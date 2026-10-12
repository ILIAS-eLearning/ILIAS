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

/**
 * Codec of the WOPI PostMessage protocol for il.HTTP.Channel: messages are
 * JSON strings like {"MessageId": "...", "SendTime": 123, "Values": {...}}.
 * The protocol has no correlation ids, so the channel is used with send() and
 * on() only, not with request().
 */
const codec = {
  /**
   * @param {string} channel
   * @param {{type: string, payload: *}} message
   * @returns {string}
   */
  encode(channel, message) {
    return JSON.stringify({
      MessageId: message.type,
      SendTime: Date.now(),
      Values: message.payload ?? {},
    });
  },

  /**
   * @param {string} channel
   * @param {*} data
   * @returns {?{type: string, payload: object}}
   */
  decode(channel, data) {
    let message = data;
    if (typeof data === 'string') {
      try {
        message = JSON.parse(data);
      } catch (e) {
        return null;
      }
    }
    if (message === null || typeof message !== 'object' || typeof message.MessageId !== 'string') {
      return null;
    }
    return { type: message.MessageId, payload: message.Values ?? {} };
  },
};

export default codec;
