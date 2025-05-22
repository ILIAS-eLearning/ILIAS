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
 * This class connects the client to the related ILIAS environment.
 * Communication is handled by sending JSON requests.
 * The response of each request is handled through callbacks delivered by the ILIASResponseHandler
 * which is passed through the constructor.
 */
export default class ILIASConnector {
  /** @type {function(string, object): Promise} */
  #send;
  /** @type {Logger} */
  #logger;

  /**
   * @param {function(string, object): Promise} send
   * @param {Logger} logger
   */
  constructor(send, logger){
    this.#send = send;
    this.#logger = logger;
  }

  /**
   * Sends a heartbeat to ILIAS in a delivered interval.
   * It is used to keep the session for an ILIAS user open.
   *
   * @param {number} interval
   */
  heartbeatInterval(interval) {
    const ignore = () => {};
    window.setInterval(() => this.#sendRequest('poll', {}, ignore), interval);
  }

  /**
   * Sends a request to ILIAS to leave a private room.
   */
  leavePrivateRoom() {
    this.#logger.logILIASRequest('leavePrivateRoom');
    this.#sendRequest('privateRoom-leave');
  }

  /**
   * Sends a request to ILIAS to invite a specific user to a private room.
   * The invitation can be done by two types
   *    1. byId
   *    2. byLogin
   *
   * @param {string} userValue
   * @param {string} invitationType
   */
  inviteToPrivateRoom(userValue, invitationType) {
    this.#sendRequest('inviteUsersToPrivateRoom-' + invitationType, {
      user: userValue
    });
  }

  /**
   * Sends a request to ILIAS to clear the chat history
   */
  clear() {
    this.#sendRequest('clear');
  }

  /**
   * Sends a request to ILIAS to kick a user from a specific room.
   * The room can either be a private or the main room.
   *
   * @param {number} userId
   */
  kick(userId) {
    this.#sendRequest('kick', {user: userId});
  }

  /**
   * Sends a request to ILIAS to ban a user from a specific room.
   * The room can either be a private or the main room.
   *
   * @param {number} userId
   */
  ban(userId) {
    this.#sendRequest('ban-active', {user: userId});
  }

  /**
   * Sends a asynchronously JSON request to ILIAS.
   *
   * @param {string} action
   * @param {{}} params
   * @param {function} responseCallback
   */
  #sendRequest(action, params = {}, responseCallback = r => this.#gotResponse(r)) {
    this.#send(action, params).then(r => r.json()).then(responseCallback);
  }

  #gotResponse(response) {
    this.#logger.logILIASResponse('default');
    if (!response.success) {
      console.error(response.reason);
      return false;
    }
    return true;
  }
}
