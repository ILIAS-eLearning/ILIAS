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

import bus from './bus';

const keygen = ((() => {
  let nr = 0;
  return () => {
    nr++;
    return 'key-' + nr;
  };
})());

const takeTicketAndOnClick = proc => takeTicketAndWait(node => node.addEventListener('click', proc));

/**
 * This class renders all available action for a user in a chat room.
 */
export default class ChatUsers {
  /** @type {NodeElement} */
  #anchor;
  /** @type {function(string, object): Promise} */
  #send;
  /** @type {function(number): boolean} */
  #isSelf;
  /** @type {ProfileImageLoader} */
  #profileImageLoader;
  /** @type {NodeElement} */
  #emptyMessage;
  /** @type {obect.<string, NodeElement>} */
  #users;
  /** @type {string[]} */
  #visibleUsers;
  /** @type {{name: string, label: string, callback: function(number)}[]} */
  #userActions;

  /**
   * @param {NodeElement} anchor
   * @param {function(string, object): Promise} send
   * @param {function(number): boolean} isSelf
   * @param {ProfileImageLoader} profileImageLoader
   * @param {{name: string, label: string, callback: function(number)}[]} userActions
   */
  constructor(anchor, send, isSelf, profileImageLoader, userActions) {
    this.#anchor = anchor;
    this.#send = send;
    this.#isSelf = isSelf;
    this.#profileImageLoader = profileImageLoader;
    this.#emptyMessage = anchor && anchor.querySelector('.no_users');
    this.#users = {};
    this.#visibleUsers = [];
    this.#userActions = userActions;
  }

  userListChanged(diff) {
    diff.removed.forEach(({key}) => this.remove(key));
    diff.added.forEach(({value}) => this.add(value));
  }

  add(user) {
    if (this.#users[user.id]) {
      return false;
    }

    const node = this.#buildUserEntry(user);
    if (!this.#isSelf(user.id)) {
      this.#visibleUsers.push(String(user.id));
    }
    this.#anchor.appendChild(node);
    this.#users[user.id] = node;
    this.#preventEmpty();

    return true;
  }

  remove(id) {
    const node = this.#users[id];
    if (!node) {
      return false;
    }

    node.remove();
    this.#visibleUsers = this.#visibleUsers.filter(x => x !== id);
    delete this.#users[id];
    this.#preventEmpty();

    return true;
  }

  setUsers(users) {
    const ids = users.map(u => String(u.id));
    Object.keys(this.#users)
      .filter(id => !ids.includes(id))
      .forEach(this.remove.bind(this));
    users.forEach(this.add.bind(this));
  }

  static actionList(txt, connector, confirmModal, startConversation) {
    return [
      {
        name: 'kick',
        callback(userId) {
          confirmModal('kick-modal').then(confirmed => {
            if (confirmed) {
              connector.kick(userId);
            }
          });
        },
      },
      {
        name: 'ban',
        callback(userId) {
          confirmModal('ban-modal').then(confirmed => {
            if (confirmed) {
              connector.ban(userId);
            }
          });
        },
      },
      {
        name: 'chat',
        callback: startConversation,
      }
    ];
  }

  #buildUserEntry(user) {
    const node = document.createElement('div');
    const itemLoaded = this.#send('view-userEntry', {
      username: user.username,
      user_id: user.id,
      actions: Object.fromEntries(this.#userActions.map(({name, callback}) => [
        name,
        takeTicketAndOnClick(() => callback(user.id))
      ])),
    }).then(r => r.text()).then(html => setHTMLWithScripts(node, html));
    node.classList.add('ilChatroomUser');

    Promise.all([itemLoaded, this.#profileImageLoader.imageOfUser(user)]).then(([_, img]) => {
      Array.from(node.querySelectorAll('img'), n => n.setAttribute('src', img));
    });
    if (this.#isSelf(user.id)) {
      node.classList.add('ilNoDisplay');
    }

    return node;
  }

  #preventEmpty() {
    this.#emptyMessage.classList[this.#visibleUsers.length ? 'add' : 'remove']('ilNoDisplay');
  }
}

function setHTMLWithScripts(node, html) {
  node.innerHTML = html;
  Array.from(node.querySelectorAll('script'), script => {
    const s = document.createElement('script');
    s.appendChild(document.createTextNode(script.innerHTML));
    script.parentNode.replaceChild(s, script);
  });

  return node;
}

function takeTicketAndWait(proc) {
  const key = keygen();
  bus.onArrived(key, proc);
  return key;
}
