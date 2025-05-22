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
import io from 'io';
import sendFromURL from './sendFromURL';
import ILIASConnector from './ILIASConnector';
import createConfirmation from './createConfirmation';
import ProfileImageLoader from './ProfileImageLoader';
import ChatUsers from './ChatUsers';
import WatchList from './WatchList';
import ChatMessageArea from './ChatMessageArea';
import ServerConnector from './ServerConnector';
import { TypeSelf, TypeNothing } from './Type';
import bindSendMessageBox from './bindSendMessageBox';
import bus from './bus';
import inviteUserToRoom from './inviteUserToRoom';
import Logger from './Logger';
import willTranslate from './willTranslate';

const setup = options => {
  const userList = new WatchList();
  const typingList = new WatchList();
  const logger = new Logger();
  const send = sendFromURL(options.apiEndpointTemplate);
  const txt = willTranslate(options.lang, il.Language.txt.bind(il.Language));
  const confirmModal = createConfirmation();
  const profileLoader = new ProfileImageLoader(
    options.initial.profile_image_url,
    options.initial.no_profile_image_url
  );
  const iliasConnector = new ILIASConnector(send, logger);
  const chatUsers = new ChatUsers(
    nodeById('chat_users'),
    send,
    id => id === options.initial.userinfo.id,
    profileLoader,
    filterAllowedActions(
      ChatUsers.actionList(txt, iliasConnector, confirmModal, willStartConversation(userList)),
      options.initial
    )
  );
  const chatArea = new ChatMessageArea(
    nodeById('chat_messages'),
    options.initial.userinfo.id,
    options.initial.state,
    profileLoader,
    typingList,
    txt,
    options.dateTimeFormatStrings,
  );
  const serverConnector = new ServerConnector(
    options.initial.userinfo,
    userList,
    chatArea,
    options.scope,
    typingList,
    options.initial.redirect_url,
    logger
  );

  return {
    bindEvents,
    processInitialData,
    connectToServer,
  };

  function bindEvents() {
    userList.onChange(chatUsers.userListChanged.bind(chatUsers));
    typingList.onChange(chatArea.typingListChanged.bind(chatArea));
    toggle('auto-scroll-toggle', on => chatArea.enableAutoScroll(on));
    toggle('system-messages-toggle', on => chatArea.enableSystemMessages(on));
    toggle('system-messages-toggle', on => saveShowSystemMessageState(on, options.initial));
    bus.onArrived('invite-modal', modalData => click('invite-button', inviteUserToRoom(
      modalData,
      {
        more: '»' + txt('autocomplete_more'),
        nothingFound: options.nothingFound,
      },
      value => iliasConnector.inviteToPrivateRoom(value.id, 'byId'),
      userList,
      ({search, all}) => send('inviteUsersToPrivateRoom-getUserList', Object.assign({q: search}, all ? {fetchall: '1'} : {})).then(r => r.json())
    )));

    click('clear-history-button', () => clearHistory(confirmModal, iliasConnector));
    bindSendMessageBox(
      nodeById('send-message-group'),
      message => serverConnector.sendMessage(message),
      options.initial.userinfo.broadcast_typing ? new TypeSelf(serverConnector) : new TypeNothing()
    );
  }

  function processInitialData() {
    popuplateInitialUserList(userList, options.initial);
    populateInitialMessages(chatArea, options.initial);
  }

  function connectToServer() {
    iliasConnector.heartbeatInterval(120 * 1000);
    serverConnector.init(
      io.connect(options.baseUrl + '/' + options.instance, {path: options.initial.subdirectory})
    );
    serverConnector.onLoggedIn(() => {
      serverConnector.enterRoom(options.scope, 0);
    });
  }
};

export const runReadOnly = options => {
  const {processInitialData} = setup(options);
  processInitialData();
};

export default options => {
  const {bindEvents, processInitialData, connectToServer} = setup(options);

  bindEvents();
  processInitialData();
  connectToServer();
  nodeById('submit_message_text').focus();
};

function filterAllowedActions(allActions, initial) {
  const allowedActions = initial.userinfo.moderator ? ['kick', 'ban', 'chat'] : ['chat'];
  return allActions.filter(option => allowedActions.includes(option.name));
}

function willStartConversation(userList) {
  return userId => il.Chat.getConversation(
    [il.OnScreenChat.user, userList.find(userId)]
  );
}

function saveShowSystemMessageState(on, initial) {
  return fetch(initial.state.system_message_update_url, {
    method: 'POST',
    body: new URLSearchParams({state: Number(on)})
  });
}

function clearHistory(confirmModal, iliasConnector) {
  confirmModal('clear-history-modal').then(yes => {
    if (yes) {
      iliasConnector.clear();
    }
  });
}

function popuplateInitialUserList(userList, initial) {
  return userList.setAll(Object.fromEntries(
    initial.users.map(user => {
      const tmp = {
        id: user.id,
        username: user.login,
        profile_picture_visible: user.profile_picture_visible,
      };
      return [tmp.id, tmp];
    })
  ));
}

function populateInitialMessages(chatArea, initial) {
  return Object.values(initial.messages).forEach(message => {
    message.timestamp = message.timestamp * 1000;
    chatArea.addMessage(message);
  });
}

function click(name, onClick) {
  bus.onArrived(name, n => n.addEventListener('click', onClick));
}

function toggle(name, onChange) {
  click(name, function() {
    onChange(this.classList.contains('on'));
  });
}

function nodeById(id) {
  return document.getElementById(id);
}
