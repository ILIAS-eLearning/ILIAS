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
 * The default codec of a channel: an envelope that marks messages of ILIAS and
 * carries the name of the channel, so messages of other senders (the content
 * itself, browser extensions, other channels) are ignored.
 *
 * A codec translates between the messages on the wire and the decoded form the
 * channel works with:
 *   {type: string, payload: *, id?: number, re?: number, error?: string}
 * Other protocols (e.g. WOPI) can be used with a channel by passing a codec
 * with the same two functions.
 */
export const MARKER = 'il-postmessage';
export const VERSION = 1;

export const envelopeCodec = {
  /**
   * @param {string} channel
   * @param {{type: string, payload: *, id?: number, re?: number, error?: string}} message
   * @returns {object}
   */
  encode(channel, message) {
    const data = {
      [MARKER]: VERSION,
      channel,
      type: message.type,
      payload: message.payload === undefined ? null : message.payload,
    };
    ['id', 're', 'error'].forEach((key) => {
      if (message[key] !== undefined) {
        data[key] = message[key];
      }
    });
    return data;
  },

  /**
   * @param {string} channel
   * @param {*} data
   * @returns {?{type: string, payload: *, id?: number, re?: number, error?: string}}
   *          null if the data is no message of this channel
   */
  decode(channel, data) {
    if (data === null
      || typeof data !== 'object'
      || data[MARKER] !== VERSION
      || data.channel !== channel
      || typeof data.type !== 'string') {
      return null;
    }
    return {
      type: data.type,
      payload: data.payload,
      id: data.id,
      re: data.re,
      error: data.error,
    };
  },
};
