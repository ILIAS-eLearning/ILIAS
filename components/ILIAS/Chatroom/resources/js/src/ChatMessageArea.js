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

import il from 'il';
import formatDateTime from './formatDateTime';

export default class ChatMessageArea {
  /** @type {NodeElement} */
  #anchor;
  /** @type {number} */
  #currentUserId;
  /** @type {{scrolling: boolean, show_auto_msg: boolean}} */
  #config;
  /** @type {ProfileImageLoader} */
  #profileImageLoader;
  /** @type {WatchList} */
  #typingList;
  /** @type {function(string): string} */
  #txt;
  /** @type {{time: string, date: string}} */
  #format;
  /** @type {null|{id: number, username: string, node: NodeElement}} */
  #lastUser;
  /** @type {function(Date): bool} */
  #lastDate;
  /** @type {NodeElement} */
  #pane;
  /** @type {NodeElement} */
  #typingInfo;
  /** @type {function(): void} */
  #touch;

  /**
   * @param {NodeElement} anchor
   * @param {number} currentUserId
   * @param {{scrolling: boolean, show_auto_msg: boolean}} config
   * @param {ProfileImageLoader} profileImageLoader
   * @param {WatchList} typingList
   * @param {function(string): string} txt
   */
  constructor(anchor, currentUserId, config, profileImageLoader, typingList, txt, format) {
    this.#anchor = anchor;
    this.#currentUserId = currentUserId;
    this.#config = config;
    this.#profileImageLoader = profileImageLoader;
    this.#typingList = typingList;
    this.#txt = txt;
    this.#format = format;
    this.#pane = createDiv(['messageContainer']);
    this.#pane.setAttribute('aria-live', 'polite');
    this.#typingInfo = createDiv(['typing-info']);
    this.#typingInfo.setAttribute('aria-live', 'polite');
    this.#touch = Void;

    this.#syncConfig();
    this.clearMessages();
    this.#show();
  }

  addMessage(message) {
    this.#touch();
    const line = createDiv(['messageLine', 'chat', !message.target || message.target.public ? 'public' : 'private']);

    const fallback = () => console.warn('Unknown message type: ', message.type);
    let lastUser = null;
    const setUser = x => {lastUser = x;};

    const cases = {
      message: () => {
        const m = msg(timeInfo(message, this.#format), actualMessage(message));
        if (this.#lastDate(new Date(message.timestamp))) {
          this.#pane.appendChild(separate(message, this.#format));
          setUser(null);
        }

        if (message.from.id === this.#currentUserId) {
          line.classList.add('myself');
        }

        if (this.#lastUser
            && this.#lastUser.id === message.from.id
            && this.#lastUser.username === message.from.username) {
          this.#lastUser.node.appendChild(m);
          setUser(this.#lastUser);
        } else {
          line.appendChild(
            messageHeader(message, this.#profileImageLoader, this.#format)
          );
          line.appendChild(m);
          this.#pane.appendChild(line);
          setUser({...message.from, node: line});
        }
      },
      connected: fallback,
      disconnected: fallback,
      private_room_entered: fallback,
      private_room_left: fallback,
      notice: () => {
        const node = createDiv(['separator', 'system-message']);
        const content = createDiv([], 'p');
        content.textContent = this.#txt(message.content, message.data);
        node.appendChild(content);
        this.#pane.appendChild(node);
      },
      error: fallback,
      userjustkicked: fallback,
    };

    (cases[message.type] || fallback)();

    this.#lastUser = lastUser;
    if (this.#config.scrolling) {
      this.#anchor.scrollTop = this.#pane.getBoundingClientRect().height;
    }
  }

  clearMessages() {
    this.#pane.textContent = '';
    this.#lastUser = null;
    this.#lastDate = remeberLastDate();

    const node = createDiv(['separator']);
    const content = createDiv([], 'p');
    content.textContent = this.#txt('welcome_to_chat');
    node.appendChild(content);
    this.#pane.appendChild(node);
    this.#touch = node.remove.bind(node);
  }

  typingListChanged() {
    const names = Object.values(this.#typingList.all());
    if (names.length === 0) {
      this.#typingInfo.textContent = '';
    } else if (names.length === 1) {
      this.#typingInfo.textContent = this.#txt("chat_user_x_is_typing", names[0]);
    } else {
      this.#typingInfo.textContent = this.#txt("chat_users_are_typing");
    }
  }

  enableAutoScroll(enable) {
    this.#config.scrolling = Boolean(enable);
    this.#syncConfig();
  }

  enableSystemMessages(enable) {
    this.#config.show_auto_msg = Boolean(enable);
    this.#syncConfig();
  }

  #show() {
    this.#anchor.appendChild(this.#pane);
    const fader = createDiv(['fader']);
    this.#anchor.appendChild(fader);
    fader.appendChild(this.#typingInfo);
  }

  #syncConfig() {
    this.#anchor.classList[this.#config.show_auto_msg ? 'remove' : 'add']('hide-system-messages');
  }
}

function remeberLastDate() {
  let last = null;
  return date => {
    const showMessage = !last
          || last.getDate() !== date.getDate()
          || last.getMonth() !== date.getMonth()
          || last.getFullYear() !== date.getFullYear();
    last = date;
    return showMessage;
  };
}

function separate(message, format) {
  const node = createDiv(['separator']);
  const content = createDiv([], 'p');
  content.textContent = formatDateTime(format.date, message.timestamp);
  node.appendChild(content);

  return node;
}

function messageHeader(message, profileImageLoader, format) {
  const dateFlag = createDiv(['user'], 'span');
  const userFlag = createDiv(['user'], 'span');
  const img = createDiv([], 'img');
  const header = createDiv(['message-header']);

  dateFlag.textContent = formatDateTime(format.time, message.timestamp);
  userFlag.textContent = message.from.username;
  img.src = profileImageLoader.defaultImage();
  profileImageLoader.imageOfUser(message.from).then(Reflect.set.bind(null, img, 'src'));

  header.appendChild(img);
  header.appendChild(userFlag);
  header.appendChild(dateFlag);

  return header;
}

const link = (() => {
  let linkNode = node => {
    try {
      il.ExtLink.autolink(node);
    } catch (e) {
      console.error('Disabling url linking. Reason:', e);
      linkNode = Void;
    }
  };

  return n => linkNode(n);
})();

function actualMessage(message) {
  const messageSpan = createDiv([], 'p');
  messageSpan.textContent = message.content;
  link(messageSpan);

  return messageSpan;
}

function msg(info, message) {
  const node = createDiv(['message-body']);
  node.appendChild(info);
  node.appendChild(message);

  return node;
}

function timeInfo(message, format) {
  const info = createDiv(['time-info']);
  info.textContent = formatDateTime(format.time, message.timestamp);

  return info;
}

function createDiv(classes, nodeType) {
  const div = document.createElement(nodeType || 'div');
  (classes || []).forEach(name => div.classList.add(name));
  return div;
}

function Void(){}
