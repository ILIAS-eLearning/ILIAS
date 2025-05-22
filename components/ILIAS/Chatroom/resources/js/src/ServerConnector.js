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
 * This class connects the client to the related chat server.
 * Communication is handled through websockets as far as *it is supported by the users browser.
 * Otherwise it uses polling method to communicate. Messages are send through `socket.emit`.
 * Messages are received through `socket.on`. There can be 3 types of messages.
 *    1. Text messages which are send by the chat users
 *    2. Notification messages. This are informational messages which are triggered by the system.
 *    3. Action messages. This messages triggers action which have to be executed in the client.
 *       This messages are triggered by the System.
 *
 */
export default class ServerConnector {
  /** @type {{login: string, id: number, profile_picture_visible: boolean}} */
  #user;
  /** @type {WatchList} */
  #userList;
  /** @type {ChatMessageArea} */
  #chatArea;
  /** @type {number} */
  #roomId;
  /** @type {WatchList} */
  #typingList;
  /** @type {string} */
  #redirectUrl;
  /** @type {Logger} */
  #logger;
  /** @type {Socket} */
  #socket;

  /**
   * @param {{login: string, id: number, profile_picture_visible: boolean}} user
   * @param {WatchList} userList
   * @param {ChatMessageArea} chatArea
   * @param {number} roomId
   * @param {WatchList} typingList
   * @param {string} redirectUrl
   * @param {Logger} logger
   */
  constructor(user, userList, chatArea, roomId, typingList, redirectUrl, logger) {
    this.#user = user;
    this.#userList = userList;
    this.#chatArea = chatArea;
    this.#roomId = roomId;
    this.#typingList = typingList;
    this.#redirectUrl = redirectUrl;
    this.#logger = logger;
  }

  init(socket) {
    this.#socket = socket;
    this.#socket.on('message', this.#onMessage.bind(this));
    this.#socket.on('connect', () => {
      this.#socket.emit('login', this.#user.login, this.#user.id, this.#user.profile_picture_visible);
    });
    this.#socket.on('user_invited', this.#onUserInvited.bind(this));
    this.#socket.on('private_room_entered', this.#onPrivateRoomEntered.bind(this));
    this.#socket.on('connected', this.#onConnected.bind(this));
    this.#socket.on('userjustkicked', this.#onUserKicked.bind(this));
    this.#socket.on('userjustbanned', this.#onUserBanned.bind(this));
    this.#socket.on('clear', this.#onClear.bind(this));
    this.#socket.on('notice', this.#onNotice.bind(this));
    this.#socket.on('userStartedTyping', this.#onUserStartedTyping.bind(this));
    this.#socket.on('userStoppedTyping', this.#onUserStoppedTyping.bind(this));
    this.#socket.on('userlist', this.#onUserlist.bind(this));
    this.#socket.on('shutdown', () => {
      this.#socket.removeAllListeners();
      this.#socket.close();
      window.location.href = this.#redirectUrl;
    });

    window.addEventListener('beforeunload', () => {
      this.#socket.close();
    });
  }

  /**
   * Sends enter room to server
   *
   */
  enterRoom() {
    // @Todo: Remove? caus' private room.
    this.#logger.logServerRequest('enterRoom');
    this.#socket.emit('enterRoom', this.#roomId);
  }

  /**
   * @param {function} callback
   */
  onLoggedIn(callback) {
    this.#socket.on('loggedIn', callback);
  }

  userStartedTyping() {
    this.#logger.logServerRequest('userStartedTyping');
    this.#socket.emit('userStartedTyping', this.#roomId);
  }

  userStoppedTyping() {
    this.#logger.logServerRequest('userStoppedTyping');
    this.#socket.emit('userStoppedTyping', this.#roomId);
  }

  sendMessage(message) {
    this.#socket.emit('message', message, this.#roomId);
  }

  /**
   * Displays chatmessage in chat
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   *   from: {id: number, name: string},
   *   format: {style: string, color: string, family: string, size: string}
   * }} messageObject
   *
   */
  #onMessage(messageObject) {
    this.#chatArea.addMessage(messageObject);
  }

  /**
   * Adds chat for user invitation
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   *   title: string
   *   owner: number
   * }} messageObject
   *
   */
  #onUserInvited(messageObject) {
    // gui.addChatMessageArea(messageObject.title, messageObject.owner);
  }

  /**
   * Enters a private Room
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   *   title: string,
   *   owner: number,
   *   subscriber: {id: number, username: string},
   *   usersInRoom: {Array}
   * }} messageObject
   *
   */
  #onPrivateRoomEntered(messageObject) {
    this.#logger.logServerResponse('onPrivateRoomEntered');
  }

  #onConnected(messageObject) {
    Object.values(messageObject.users).forEach(user => {
      let data = {
        id: user.id,
        username: user.login,
        profile_picture_visible: user.profile_picture_visible,
      };

        this.#userList.add(data);
        this.#chatArea.addMessage({login: data.label, timestamp: messageObject.timestamp, type: 'connected'});
    });
  }

  /**
   * Kicks a user from chat
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   * }} messageObject
   *
   */
  #onUserKicked(messageObject) {
    this.#logger.logServerResponse('onUserKicked');

    // If user is kicked from sub room, redirect to main room

    this.#userList.remove(this.#user.id);
    window.location.href = this.#redirectUrl + "&msg=kicked";
  }

  /**
   * Banns a user from chat
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   * }} messageObject
   *
   */
  #onUserBanned(messageObject) {
    if (this.#socket) {
      this.#socket.removeAllListeners();
      this.#socket.close();
    }
    window.location.href = this.#redirectUrl + "&msg=banned";
  }

  /**
   * Clears chat history
   */
  #onClear() {
    this.#chatArea.clearMessages();
  }

  /**
   * Adds a notice to chat
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   *   data: {}
   * }} messageObject
   *
   */
  #onNotice(messageObject) {
    this.#chatArea.addMessage(messageObject);
  }

  #onUserStartedTyping(message) {
    this.#logger.logServerResponse("onUserStartedTyping");

    const subscriber = JSON.parse(message.subscriber);
    this.#typingList.add(subscriber.id, subscriber.username);
  }

  #onUserStoppedTyping(message) {
    this.#logger.logServerResponse("onUserStoppedTyping");

    const subscriber = JSON.parse(message.subscriber);
    this.#typingList.remove(subscriber.id);
  }

  /**
   * Updates the list of users.
   *
   * @param {{
   *   type:string,
   *   timestamp: number,
   *   content: string,
   *   roomId: number,
   *   users: {}
   * }} messageObject
   *
   */
  #onUserlist(messageObject) {
    const users = messageObject.users;
    this.#logger.logServerResponse("onUserlist");

    this.#userList.setAll(Object.fromEntries(Object.values(users).map(otherUser => {
      const chatUser = {
        id: otherUser.id,
        username: otherUser.username,
        profile_picture_visible: otherUser.profile_picture_visible,
      };

      return [chatUser.id, chatUser];
    })));
  }
};
