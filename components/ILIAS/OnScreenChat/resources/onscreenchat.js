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

(function(triggerSignal, $scope, $chat, dateTimeFormatter){
	'use strict';

	// Ensure historyRequested is always set when calling getHistory, but
	// don't pollute external prototype chain of $chat, keep it inside a local object.
	$chat = Object.create($chat);
	$chat.getHistory = (original => (conversationId, oldestMessageTimestamp, reverseSorting = false) => {
		getModule().historyRequested = {conversationId, oldestMessageTimestamp, reverseSorting};
		return original.call($chat, conversationId, oldestMessageTimestamp, reverseSorting);
	})($chat.getHistory);

	const TYPE_CONSTANT = 'osc';
	const PREFIX_CONSTANT = TYPE_CONSTANT + '_';
	const ACTION_SHOW_CONV = 'show';
	const ACTION_HIDE_CONV = 'hide';
	const ACTION_REMOVE_CONV = 'remove';
	const ACTION_STORE_CONV = 'store';
	const ACTION_DERIVED_FROM_CONV_OPEN_STATUS = 'derivefromopen';
	const resizeTextareas = {}; // string: function
	const MAX_CHAT_LINES = 3;
	let menu_collector_events_added = false;

	const tryLink = (() => {
		let link = node => {
			try {
				il.ExtLink.autolink(node);
			} catch (error) {
				console.error('Disabling url linking. Reason:', error);
				link = () => {};
			}
		};

		return node => link(node);
	})();

	const triggerMap = {
		participantEvent: ['click', '[data-onscreenchat-userid]'],
		onEmitCloseConversation: ['click', '[data-onscreenchat-minimize]'],
		submitEvent: ['click', '[data-action="onscreenchat-submit"]', 'keydown', '[data-onscreenchat-window]'],
		addEvent: ['click', '[data-onscreenchat-add]'],
		windowClicked: ['click', '[data-onscreenchat-window]'],
		resizeChatWindow: ['input', '[data-onscreenchat-message]'],
		messageInput: ['keyup click', '[data-onscreenchat-message]'],
		focusOut: ['focusout', '[data-onscreenchat-window]'],
		menuItemClicked: ['click', '[data-onscreenchat-menu-item]'],
		menuItemRemovalRequest: [],
		messageKeyUpEvent: ['keyup', '[data-onscreenchat-message]'],
	};
	$scope.il.OnScreenChatJQueryTriggers = {
		triggers: mapObject(triggerMap, () => () => {}),

		setTriggers: function(triggers) {
			Object.keys(triggerMap).map(function(key){
				if (triggers.hasOwnProperty(key)) {
					$scope.il.OnScreenChatJQueryTriggers.triggers[key] = triggers[key];
				}
			});

			return this;
		},

		init: function() {
			window.addEventListener('resize', $scope.il.OnScreenChat.resizeWindow);
			$scope.il.OnScreenChat.resizeWindow();

			const onBody = (event, selector, thunk) => {
				document.body.addEventListener(event, event => {
					const node = event.target.closest(selector)
					if (node) {
						return thunk.call(node, event);
					}
				});
			};
			mapObject(triggerMap, function(eventAndSelector, key){
				piecesOf(2, eventAndSelector).forEach(function(eventAndSelector){
					onBody(eventAndSelector[0], eventAndSelector[1], $scope.il.OnScreenChatJQueryTriggers.triggers[key]);
				});
			});
		}
	};

	function createNode(type, properties, children)
	{
		const n = document.createElement(type);
		Object.entries(properties || {}).forEach(([k, v]) => n.setAttribute(k, v));
		(children || []).forEach(c => n.appendChild(c));
		return n;
	}

	$scope.il.OnScreenChat = {
		config: {},
		container: createNode('div', {class: 'iosOnScreenChat'}), // @todo replace usages with non jquery
		storage: undefined,
		user: undefined,
		historyBlocked: false,
		historyRequested: null,
		inputHeight: undefined,
		historyTimestamps: {},
		printedMessages: {},
		participantsImages: {},
		participantsNames: {},
		chatWindowWidth: 278,
		numWindows: Infinity,
		conversationItems: {},
		conversationMessageTimes: {},
		conversationToUiIdMap: {},
		bus: il.Chatroom.createBus(),

		setConversationMessageTimes: function(timeInfo) {
			getModule().conversationMessageTimes = timeInfo;
		},

		addConversationToUiIdMapping: function(conversationId, uiId) {
			getModule().conversationToUiIdMap[conversationId] = uiId;
		},

		init: function(config) {
			getModule().config = config;
			dateTimeFormatter.setLocale(config.locale);

			getModule().storage = new ConversationStorage();

			const loadModal = busName => il.Chatroom.sendFromURL(getModule().config.modalURLTemplate)(busName).then(r => r.text()).then(modalHTML => {
				const dummyParent = document.createElement('div');
				dummyParent.innerHTML = modalHTML;
				const modal = dummyParent.children[0];
				Array.from(dummyParent.querySelectorAll('script'), script => {
					const s = document.createElement('script');
					s.appendChild(document.createTextNode(script.innerHTML));
					script.parentNode.replaceChild(s, script);
				});
				dummyParent.childNodes.forEach(n => document.body.appendChild(n));
								return new Promise(resolve => getModule().bus.onArrived(busName, ([showSignal, closeSignal]) => resolve({
					showModal: () => triggerSignal(showSignal),
					closeModal: () => triggerSignal(closeSignal),
					node: modal,
				})))
			});

			const confirmModal = lazy(() => {
				let currentThen = null;
				const modal = loadModal('confirmRemove');
				modal.then(({node, closeModal}) => node.querySelector('form').addEventListener('submit', e => {
					const isSubmit = document.activeElement.matches('input[type="submit"]');
					e.preventDefault();
					closeModal();
					if (isSubmit) {
						currentThen();
					}
				}));

				return then => {
					currentThen = then;
					modal.then(({showModal}) => showModal());
				}
			});

			const inviteModal = lazy(() => {
				let currentConversationId = null;
				const setup = loadModal('inviteModal').then(modalInfo => {
					const dummyParent = document.createElement('div');
					dummyParent.innerHTML = getModule().config.nothingFoundTemplate;
					return il.Chatroom.inviteUserToRoom(
						modalInfo,
						{
							more: '»' + il.Language.txt('autocomplete_more'),
							nothingFound: dummyParent.children[0],
						},
						entry => getModule().addUser(currentConversationId, entry.id, entry.value),
						((getModule().storage.get(currentConversationId) || {}).participants || []).map(p => p.id),
						({search, all}) => il.Chatroom.sendFromURL(getModule().config.userListURL)(
							'',
							Object.assign({term: search}, all ? {fetchall: '1'} : {})
						).then(r => r.json())
					)
				});

				return conversationId => {
					currentConversationId = conversationId;
					setup.then(open => open());
				};
			});

			getModule().openConfirmModal = then => confirmModal()(then);
			getModule().openInviteUserModal = conversationId => inviteModal()(conversationId);

			Object.entries(getModule().config.initialUserData).forEach(([usrId, item]) => {
				getModule().participantsNames[usrId] = item.public_name;

				const img = new Image();
				img.src = item.profile_image;
				getModule().participantsImages[usrId] = img;
			});

			window.addEventListener('storage', function(e) {
				if (
					typeof e.key !== "string" ||
					e.key.indexOf(PREFIX_CONSTANT) !== 0
				) {
					console.log("Ignored local storage event not being in namespace: " + PREFIX_CONSTANT);
					return;
				}

				var conversation = e.newValue;

				if (typeof conversation === "string") {
					conversation = JSON.parse(conversation);
				}

				if (conversation instanceof Object && conversation.hasOwnProperty('type') && conversation.type === TYPE_CONSTANT) {
					if (ACTION_SHOW_CONV === conversation.action) {
						getModule().onOpenConversation(conversation);
					} else if (ACTION_HIDE_CONV === conversation.action) {
						getModule().onCloseConversation(conversation);
					} else if (ACTION_REMOVE_CONV === conversation.action) {
						getModule().onRemoveConversation(conversation);
					}

					if (e.callback instanceof Function) {
						e.callback();
					}
				}
			});

			setInterval(() => {
				const gotoLogin = () => {
					window.location = './login.php';
				};
				fetch(getConfig().verifyLoginURL)
					.then(r => r.json())
					.then(x => x.loggedIn || gotoLogin())
					.catch(gotoLogin);
			}, 300000);

			setInterval(() => {
				document.querySelectorAll('[data-livestamp]').forEach(node => {
					node.textContent = dateTimeFormatter.fromNowToTime(Number(node.dataset.livestamp));
				});
				document.querySelectorAll('[data-message-time]').forEach(node => {
					node.setAttribute('title', dateTimeFormatter.format(Number(node.dataset.messageTime), 'LT'));
				});
			}, 60000);

			$chat.init(getConfig().userId, getConfig().username, getModule().onLogin);
			window.addEventListener('beforeunload', getModule().onUnload);
			$chat.receiveMessage(getModule().receiveMessage);
			$chat.onParticipantsSuppressedMessages(getModule().onParticipantsSuppressedMessages);
			$chat.onSenderSuppressesMessages(getModule().onSenderSuppressesMessages);
			$chat.receiveConversation(getModule().onConversation);
			$chat.onUserStartedTyping(getModule().onUserStartedTyping);
			$chat.onUserStoppedTyping(getModule().onUserStoppedTyping);
			$chat.onHistory(getModule().onHistory);
			$chat.onGroupConversation(getModule().onConversationInit);
			$chat.onGroupConversationLeft(getModule().onConversationLeft);
			$chat.onConversationInit(getModule().onConversationInit);

			$scope.il.OnScreenChatJQueryTriggers.setTriggers({
				participantEvent:        getModule().startConversation,
				onEmitCloseConversation: getModule().onEmitCloseConversation,
				submitEvent:             getModule().handleSubmit,
				messageKeyUpEvent:       getModule().onMessageKeyUp,
				addEvent:                getModule().openInviteUser,
				resizeChatWindow:        getModule().resizeMessageInput,
				focusOut:                getModule().onFocusOut,
				messageInput:            getModule().onMessageInput,
				menuItemRemovalRequest:  getModule().onMenuItemRemovalRequest,
				windowClicked:           getModule().onWindowClicked,
				menuItemClicked:         getModule().onMenuItemClicked,
			}).init();

			document.body.appendChild(createNode(
				'div',
				{id: 'onscreenchat-container'},
				[getModule().container]
			));
		},

		/**
		 * Called if a 'Start a Conversation' UI element is clicked by a conversation initiator
		 * @param e
		 */
		startConversation: function(e) {
			e.preventDefault();
			e.stopPropagation();

			let conversationId = this.getAttribute('data-onscreenchat-conversation');

			if (!conversationId && this.closest('[data-id]') !== null) {
				conversationId = this.closest('[data-id]').dataset.id;
			}

			let conversation = getModule().storage.get(conversationId);

			if (conversation == null) {
				let participant = {
					id: this.getAttribute('data-onscreenchat-userid'),
					name: this.getAttribute('data-onscreenchat-username')
				};

				if (typeof participant.id !== "undefined" && participant.id.length > 0) {
					$chat.getConversation([getModule().user, participant]);
				}
				return;
			}

			conversation.action = ACTION_SHOW_CONV;
			getModule().storage.save(conversation);
		},

		open: function(conversation) {
			let conversationWindow = document.querySelector('[data-onscreenchat-window="' + conversation.id + '"]');
			let newDomElementsCreated = false;

			if (conversationWindow && !conversationWindow.matches('.ilNoDisplay')) {
				return;
			}

			if (conversationWindow === null) {
				conversationWindow = getModule().createWindow(conversation);
				conversationWindow.flatMap(n => Array.from(n.querySelectorAll('.panel-body'))).forEach(n => {
					n.addEventListener('dblclick', () => n.dispatchEvent(new Event('scroll')))
					n.addEventListener('scroll', getModule().onScroll);
				});

				conversationWindow.forEach(n => getModule().container.appendChild(n));
				getModule().addMessagesOnOpen(conversation);

				newDomElementsCreated = true;
			} else {
				conversationWindow = [conversationWindow];
			}

			if (conversation.latestMessage != null) {
				let reverseHistorySorting = newDomElementsCreated,
					ts = null;

				if (!newDomElementsCreated && getModule().historyTimestamps.hasOwnProperty(conversation.id)) {
					ts = getModule().historyTimestamps[conversation.id];
				}

				$chat.getHistory(
					conversation.id,
					ts,
					reverseHistorySorting
				); 
			}

			conversationWindow.flatMap(n => Array.from(n.querySelectorAll('[aria-live]'))).forEach(n => {
				n.setAttribute('aria-live', 'polite');
			});
			conversationWindow.forEach(n => {n.classList.remove('ilNoDisplay');});

			if(countOpenChatWindows() > getModule().numWindows) {
				getModule().closeWindowWithLongestInactivity();
			}

			resizeTextareas[conversation.id] = il.Chatroom.expandableTextarea(
				'.panel-footer-for-shadow',
				'[data-onscreenchat-window="' + conversation.id + '"] [data-onscreenchat-message]',
				MAX_CHAT_LINES
			);

			conversationWindow.flatMap(n => Array.from(n.querySelectorAll('[data-onscreenchat-message]'))).forEach(n => {
				getModule().resizeMessageInput.call(n);
			});
			conversationWindow.forEach(getModule().scrollBottom);

		},

		scrollBottom: function(chatWindow) {
			// Prevented issue with non existing elements (when there is no conv. on document ready)
			const oscBody = chatWindow.querySelector('[data-onscreenchat-body]');
			if (oscBody) {
				chatWindow.querySelectorAll('.panel-body').forEach(n => {
					n.scrollTop = oscBody.scrollHeight;
				});
			}
		},

		resizeMessageInput: function(e){
			const inputWrapper = this.closest('.panel-footer');
			const parent = inputWrapper.closest('[data-onscreenchat-window]');
			resizeTextareas[parent.dataset.onscreenchatWindow]();
			const wrapperHeight = parent.getBoundingClientRect().height;
			const headingHeight = parent.querySelector('.panel-heading').getBoundingClientRect().height;
			const inputHeight = inputWrapper.getBoundingClientRect().height;
			const bodyHeight = wrapperHeight - inputHeight - headingHeight;

			if(this.innerHTML === '<br>') {
				this.innerHTML = '';
			}

			parent.querySelectorAll('.panel-body').forEach(n => {n.style.height = bodyHeight + 'px';});
		},

		createWindow: function(conversation) {
			var template = getModule().config.chatWindowTemplate;
			if (conversation.isGroup) {
				var participantsNames = getParticipantsNames(conversation);
				var partTooltipFormatter = new ParticipantsTooltipFormatter(participantsNames);
				template = template.replace(/\[\[participants-tt\]\]/g, partTooltipFormatter.format());
				template = template.replace(
					/\[\[participants-header\]\]/g,
					il.Language.txt('chat_osc_head_grp_x_persons', participantsNames.length)
				);
			} else {
				var participantsNames = getParticipantsNames(conversation, function(usrId) {
					return getModule().user === undefined || getModule().user.id != usrId;
				});

				template = template.replace(/\[\[participants-tt\]\]/g, participantsNames.join(', '));
				template = template.replace(/\[\[participants-header\]\]/g, participantsNames.join(', '));
			}
			template = template.replace(/\[\[conversationId\]\]/g, conversation.id);
			template = template.replace('#:#chat_osc_write_a_msg#:#', il.Language.txt('chat_osc_write_a_msg'));
			const dummyParent = document.createElement('div');
			dummyParent.innerHTML = template;

			const setAttributes = obj => node => Object.entries(obj)
			      .forEach(([k, v]) => node.setAttribute(k, v));

			dummyParent.querySelectorAll('[data-action="addUser"]').forEach(setAttributes({
				"title":                 il.Language.txt('chat_osc_add_user'),
				"data-onscreenchat-add": conversation.id,
				"data-toggle":           "tooltip",
				"data-placement":        "auto"
			}));
			dummyParent.querySelectorAll('.minimize').forEach(setAttributes({
				"title":                   il.Language.txt('chat_osc_minimize'),
				"data-onscreenchat-minimize": conversation.id,
				"data-toggle":             "tooltip",
				"data-placement":          "auto"
			}));

			return Array.from(dummyParent.children);
		},
		rerenderConversations: function(conversation) {
			let conversations = Object.values(getModule().conversationItems).filter(function(conversation) {
				return conversation.latestMessage !== null && (conversation.open === false || conversation.open === undefined);
			}).sort(function(a, b) {
				return b.latestMessage.timestamp - a.latestMessage.timestamp;
			});

			try {
				let conversationIds = conversations.map(function (conversation) {
					return conversation.id;
				}).join(",");

				let xhr = new XMLHttpRequest();
				xhr.open('GET', getConfig().renderConversationItemsURL + '&ids=' + conversationIds);
				xhr.onload = function () {
					if (getModule().menuCollector === undefined) {
						console.error("No menu collector found in the UI, please ensure the main bar item is enabled in the ILIAS administration!");
						return;
					}

					if (xhr.status === 200) {
						getModule().menuCollector.innerHTML = xhr.responseText;
						getModule().menuCollector.querySelectorAll('script').forEach(element => {
							eval(element.innerHTML);
						})
						if (!menu_collector_events_added) {
							getModule().menuCollector.addEventListener('click', e => {
								const a = e.target.closest('[data-id]');
								const b = e.target.closest('[data-id] .close');
								if (b) {
									$scope.il.OnScreenChatJQueryTriggers.triggers.menuItemRemovalRequest.call(b, e);
								} else if (a) {
									$scope.il.OnScreenChatJQueryTriggers.triggers.menuItemClicked.call(a, e);
								}
							});
							menu_collector_events_added = true;
						}
					} else {
						getModule().menuCollector.innerHTML = '';
						console.error(xhr.status + ': ' + xhr.responseText);
					}
				};
				xhr.send();
			} catch (e) {
				console.error(e);
			}
		},

		removeMenuEntry: () => {
			event.target.closest('[data-id]').remove();
		},

		/**
		 * Is called (for each browser tab) if an 'Conversation Remove' action was emitted as LocalStorage event
		 * @param conversation
		 */
		onRemoveConversation: function(conversation) {
			const conversationWindow = document.querySelector('[data-onscreenchat-window="' + conversation.id + '"]');

			if (conversationWindow) {
				conversationWindow.querySelectorAll('[aria-live]').forEach(n => n.setAttribute('aria-live', 'off'));
				conversationWindow.classList.add('ilNoDisplay');
			}
			// Remove conversation
			if (getModule().conversationItems.hasOwnProperty(conversation.id)) {
				delete getModule().conversationItems[conversation.id];
			}
			getModule().rerenderConversations(conversation);
		},

		/**
		 * Is called (for each browser tab) if an 'Conversation Close' action was emitted as LocalStorage event
		 * @param conversation
		 */
		onCloseConversation: function(conversation) {
			const conversationWindow = document.querySelector('[data-onscreenchat-window="' + conversation.id + '"]');

			if (conversationWindow) {
				conversationWindow.querySelectorAll('[aria-live]')
					.forEach(n => n.setAttribute('aria-live', 'off'));
				conversationWindow.classList.add('ilNoDisplay');
			}

			// Add or update conversation
			if (!getModule().conversationItems.hasOwnProperty(conversation.id)) {
				getModule().conversationItems[conversation.id] = conversation;
			}
			DeferredCallbackFactory('renderConversations')(function () {
				getModule().rerenderConversations(conversation);
			}, 100);
		},

		/**
		 * Is called (for each browser tab) if an 'Conversation Open' action was emitted as LocalStorage event
		 * @param conversation
		 */
		onOpenConversation: function(conversation) {
			getModule().open(conversation);

			// Remove conversation
			if (getModule().conversationItems.hasOwnProperty(conversation.id)) {
				delete getModule().conversationItems[conversation.id];
			}

			getModule().rerenderConversations(conversation);
		},

		/**
		 * Triggered if a conversation window should be closed by an UI event in ONE tab
		 * Triggers itself a localStorage event, which results in a call to onCloseConversation for ALL browser tabs
		 * @param e
		 */
		onEmitCloseConversation: function(e) {
			e.preventDefault();
			e.stopPropagation();

			const conversation = getModule().storage.get(this.getAttribute('data-onscreenchat-minimize'));

			conversation.action = ACTION_HIDE_CONV;
			getModule().storage.save(conversation);
		},

		handleSubmit: function(e) {
			const isEnter = e.keyCode === 13;
			const shiftEnter = isEnter && e.shiftKey;
			const altEnter = isEnter && e.altKey;
			if (shiftEnter || altEnter) {
				const input = this.querySelector('[data-onscreenchat-message]');
				insertAtCursor(input, '\n');
				getModule().resizeMessageInput.call(input);
				e.preventDefault();
				return false;
			} else if (isEnter || e.type === 'click') {
				e.preventDefault();
				const conversationId = this.closest('[data-onscreenchat-window]').getAttribute('data-onscreenchat-window');
				getModule().send(conversationId);
				getModule().historyBlocked = true;
			}
		},

		send: function(conversationId) {
			const input = document.querySelector('[data-onscreenchat-window="' + conversationId + '"] [data-onscreenchat-message]');
			const message = input.value;

			if(message !== "") {
				$chat.sendMessage(conversationId, message);
				input.value = '';
				getModule().onMessageInput.call(input);
				getModule().resizeMessageInput.call(input);
			}
		},

		addMessagesOnOpen: function(conversation) {
			let messages = conversation.messages;

			for (let index in messages) {
				if (messages.hasOwnProperty(index)) {
					getModule().addMessage(conversation, messages[index], false);
				}
			}
		},

		receiveMessage: function(messageObject) {
			let conversation = getModule().storage.get(messageObject.conversationId),
				username = findUsernameInConversationByMessage(messageObject);

			if (username !== "") {
				if (undefined === getModule().historyTimestamps[conversation.id]) {
					getModule().historyTimestamps[conversation.id] = messageObject.timestamp;
				}

				conversation.latestMessage = messageObject;

				conversation.action = ACTION_SHOW_CONV;
				getModule().storage.save(conversation, () => {
					getModule().addMessage(conversation, messageObject, false);
				});

				if (
					(!messageObject.hasOwnProperty("isSystem") || !messageObject.isSystem) &&
					getModule().user !== undefined &&
					getConfig().enabledBrowserNotifications &&
					parseInt(getModule().user.id) !== parseInt(messageObject.userId)
				) {
					const dummy = document.createElement('span');
					dummy.innerHTML = messageObject.message;
					il.OnScreenChatNotifications.send(
						messageObject.id,
						conversation.id,
						il.Language.txt('osc_noti_title'),
						dummy.textContent,
						getConfig().notificationIconPath
					);
				}
			}
		},

		onParticipantsSuppressedMessages: function(messageObject) {
			messageObject.isSystem = true;

			if (messageObject.hasOwnProperty("ignoredParticipants")) {
				var ignoredParticipants = messageObject["ignoredParticipants"];

				if (Object.keys(ignoredParticipants).length > 0) {
					var conversation = getModule().storage.get(messageObject.conversationId);

					if (conversation.isGroup) {
						messageObject.message = il.Language.txt('chat_osc_subs_rej_msgs_p').replace(
							/%s/ig, Object.values(ignoredParticipants).map(
								val => findUsernameByIdByConversation(conversation, val) || null
							).join(', ')
						);
					} else {
						messageObject.message = il.Language.txt('chat_osc_subs_rej_msgs');
					}
					getModule().receiveMessage(messageObject);
				}
			}
		},

		onSenderSuppressesMessages: function(messageObject)  {
			messageObject.isSystem = true;

			messageObject.message = il.Language.txt('chat_osc_self_rej_msgs');
			getModule().receiveMessage(messageObject);
		},

		onMessageKeyUp: function (event) {
			if (getConfig().broadcast_typing !== true) {
				return;
			}

			const conversationId = this.closest('[data-onscreenchat-window]').getAttribute('data-onscreenchat-window');
			const broadcaster = TypingBroadcasterFactory.getInstance(
				conversationId,
				function() {
					$chat.userStartedTyping(conversationId);
				},
				function() {
					$chat.userStoppedTyping(conversationId);
				}
			);

			const keycode = event.keyCode || event.which;
			if (keycode === 13) {
				broadcaster.release();
				return;
			}

			const input = document.querySelector('[data-onscreenchat-window="' + conversationId + '"] [data-onscreenchat-message]');
			if (input.value.trim() === '') {
				return '';
			}

			broadcaster.registerTyping();
		},

		onUserStartedTyping: function (message) {
			const generator = TypingUsersTextGeneratorFactory.getInstance(message.conversation.id);

			generator.addTypingUser(message.participant.id);

			getModule().renderTypingInfo(
				message.conversation,
				generator.text(
					getModule().storage,
					il.Language,
					getParticipantsNames
				)
			);
		},

		onUserStoppedTyping: function (message) {
			const generator = TypingUsersTextGeneratorFactory.getInstance(message.conversation.id);

			generator.removeTypingUser(message.participant.id);

			getModule().renderTypingInfo(
				message.conversation,
				generator.text(
					getModule().storage,
					il.Language,
					getParticipantsNames
				)
			);
		},
		
		renderTypingInfo: function (conversation, text) {
			const node = document.querySelector('[data-onscreenchat-window="' + conversation.id + '"] [data-onscreenchat-typing]') || {};
			node.textContent = text;
		},

		/**
		 * 
		 * @param conversation
		 * @returns {jQuery.Deferred}
		 */
		requestUserProfileData: function(conversation) { // @todo update caller
			const dfd = Promise.withResolvers();
			const participantsIds = getParticipantsIds(conversation).filter(
				id => !getModule().participantsImages.hasOwnProperty(id)
			);

			if (participantsIds.length === 0) {
				dfd.resolve();

				return dfd.promise;
			}

			fetch(getModule().config.userProfileDataURL + '&usr_ids=' + participantsIds.join(','))
				.then(r => r.json())
				.then(response => {
					Object.entries(response).forEach(([id, item]) => {
						getModule().participantsNames[id] = item.public_name;
						const img = new Image();
						img.src = item.profile_image;
						getModule().participantsImages[id] = img;
						const node = document.querySelector('[data-onscreenchat-avatar="'+id+'"]');
						node && node.setAttribute('src', img.src);
				});

				dfd.resolve();
			});

			return dfd.promise;
		},

		/**
		 * Triggered by a socket event
		 * Called for the initiator of a new conversation
		 * Also called for the initiating user after after initiating a group conversation (results in a new chat window)
		 * @param conversation
		 */
		onConversationInit: function(conversation){
			// Directly save the conversation on storage to prevent race conditions
			conversation.action = ACTION_STORE_CONV;
			conversation.lastActivity = new Date().getTime();
			getModule().storage.save(conversation);

			getModule().requestUserProfileData(conversation)
				.then(() => {
					conversation.action = ACTION_SHOW_CONV;
					getModule().storage.save(conversation);
				});
		},

		onMenuItemRemovalRequest: function(e) {
			e.preventDefault();
			e.stopPropagation();

			const conversationId = this.dataset.onscreenchatConversation
			      || (this.closest('[data-onscreenchat-conversation]') || {dataset: {}}).dataset.onscreenchatConversation
			      || (this.closest('[data-id]') || {dataset: {}}).dataset.id;

			if (!conversationId) {
				return;
			}

			const conversation = getModule().storage.get(conversationId);
			if (conversation.isGroup) {
				getModule().openConfirmModal(() => {
					$chat.closeConversation(conversationId, getModule().user.id);
					$chat.removeUser(conversationId, getModule().user.id, getModule().user.name);
				});
			} else {
				$chat.closeConversation(conversationId, getModule().user.id);

				conversation.action = conversation.action = ACTION_REMOVE_CONV;
				getModule().storage.save(conversation);
			}
		},

		onWindowClicked: function(e) {
			if (
				!e.target.closest('[data-onscreenchat-header]') &&
				!e.target.parentNode.matches('[data-onscreenchat-body-msg]')
			) {
				e.preventDefault();
				e.stopPropagation();

				this.querySelector('[data-onscreenchat-message]').focus();
			}
		},

		onMenuItemClicked: function(e) {
			$scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent.call(this, e);
		},

		onConversationLeft: function(conversation) {
			conversation.action = conversation.action = ACTION_REMOVE_CONV;
			getModule().storage.save(conversation);
		},

		onFocusOut: function() {
			var conversation = getModule().storage.get(this.getAttribute('data-onscreenchat-window'));
			getModule().trackActivityFor(conversation);
		},

		onConversation: function(conversation) {
			// Directly save the conversation on storage to prevent race conditions
			conversation.action = ACTION_STORE_CONV;
			getModule().storage.save(conversation);

			const chatWindow = document.querySelector('[data-onscreenchat-window="'+conversation.id+'"]');

			getModule().requestUserProfileData(conversation)
				.then(() => {
					if (chatWindow) {
						var participantsNames, header, tooltip;
						if (conversation.isGroup) {
							participantsNames = getParticipantsNames(conversation);

							header = il.Language.txt('chat_osc_head_grp_x_persons', participantsNames.length);
							var partTooltipFormatter = new ParticipantsTooltipFormatter(participantsNames);
							tooltip = partTooltipFormatter.format();
						} else {
							participantsNames = getParticipantsNames(conversation, function(usrId) {
								return getModule().user === undefined || getModule().user.id != usrId;
							});
							tooltip = header = participantsNames.join(', ');
						}

						const node = chatWindow.querySelector('[data-onscreenchat-window-participants]');
						node.innerHTML = header;
						node.setAttribute('title', tooltip);
						node.setAttribute('data-original-title', tooltip);
					}

					conversation.action = ACTION_DERIVED_FROM_CONV_OPEN_STATUS;
					getModule().storage.save(conversation);
				});
		},

		onHistory: function (conversation) {
			getModule().historyRequested = null;
			const container = document.querySelector('[data-onscreenchat-window="' + conversation.id + '"]');
			const messages = Object.values(conversation.messages);
			const messagesHeight = container.querySelector('[data-onscreenchat-body]').getBoundingClientRect().height;

			messages.forEach(function(message) {
				getModule().addMessage(conversation, message, !conversation.reverseSorting);
			});

			if (
				undefined === getModule().historyTimestamps[conversation.id] ||
				conversation.oldestMessageTimestamp < getModule().historyTimestamps[conversation.id]
			) {
				const newMessagesHeight = container.querySelector('[data-onscreenchat-body]').getBoundingClientRect().height;
				container.querySelector('.panel-body').scrollTop = newMessagesHeight - messagesHeight;
				getModule().historyTimestamps[conversation.id] = conversation.oldestMessageTimestamp;
			}

			getModule().historyBlocked = false;

			const menuLoader = container.querySelector('.ilOnScreenChatMenuLoader');
			menuLoader && menuLoader.closest('div').remove();
		},

		onScroll: function() {
			const container = this.closest('[data-onscreenchat-window]');
			const conversation = getModule().storage.get(container.getAttribute('data-onscreenchat-window'));

			if (this.scrollTop === 0 && !getModule().historyBlocked && conversation.latestMessage != null) {
				getModule().historyBlocked = true;
				this.prepend(
					createNode('div', {style: 'text-align: center; margin-top: -10px'}, [
						createNode('img', {class: 'ilOnScreenChatMenuLoader', src: getConfig().loaderImg})
					])
				);

				const oldestMessageTimestamp = getModule().historyTimestamps[conversation.id];
				$chat.getHistory(conversation.id, oldestMessageTimestamp);
			}
		},

		onLogin: function(participant) {
			getModule().user = participant;
		},

		onUnload: function(participant) {
			TypingBroadcasterFactory.releaseAll();
		},

		openInviteUser: function(e) {
			e.preventDefault();
			e.stopPropagation();

			getModule().openInviteUserModal(this.getAttribute('data-onscreenchat-add'));
		},

		trackActivityFor: function(conversation){
			conversation.action = ACTION_STORE_CONV;
			conversation.lastActivity = new Date().getTime();
			getModule().storage.save(conversation);

			DeferredActivityTrackerFactory.getInstance(conversation.id).track(function() {
				$chat.trackActivity(conversation.id, getModule().user.id, conversation.lastActivity);
			});
		},

		getCaretPosition: function(elm) {
			let caretPos = 0;
			let sel;
			let range;

			if (window.getSelection) {
				sel = window.getSelection();
				if (sel.rangeCount) {
					range = sel.getRangeAt(0);
					if (range.commonAncestorContainer.parentNode == elm) {
						caretPos = range.endOffset;
					}
				}
			} else if (document.selection && document.selection.createRange) {
				range = document.selection.createRange();
				if (range.parentElement() == elm) {
					const tempEl = document.createElement("span");
					elm.insertBefore(tempEl, elm.firstChild);
					const tempRange = range.duplicate();
					tempRange.moveToElementText(tempEl);
					tempRange.setEndPoint("EndToEnd", range);
					caretPos = tempRange.text.length;
				}
			}
			return caretPos;
		},

		onMessageInput: function() {
			this.setAttribute('data-onscreenchat-last-caret-pos', getModule().getCaretPosition(this));
		},

		shouldPrintMessage: function (conversation, messageObject, prepend) {
			let doPrintMessage = true;
			const username = findUsernameInConversationByMessage(messageObject);

			if (username === '') {
				return false;
			}

			if((getModule().historyRequested || {}).oldestMessageTimestamp === null){
				return false;
			}

			if (!getModule().printedMessages.hasOwnProperty(conversation.id)) {
				getModule().printedMessages[conversation.id] = {};
			}

			if (getModule().printedMessages[conversation.id].hasOwnProperty(messageObject.id)) {
				doPrintMessage = false;
			}

			getModule().printedMessages[conversation.id][messageObject.id] = messageObject.id;
			
			return doPrintMessage;
		},

		addMessage: function(conversation, messageObject, prepend) {
			let template = getModule().config.messageTemplate;
			const position = (messageObject.userId == getModule().config.userId)? 'right' : 'left';
			const message = messageObject.message.replace(/(?:\r\n|\r|\n)/g, '<br />');
			const chatWindow = document.querySelector('[data-onscreenchat-window="' + messageObject.conversationId + '"]');
			const chatBody = chatWindow.querySelector("[data-onscreenchat-body]");
			const items = [];

			if (!getModule().shouldPrintMessage(conversation, messageObject, prepend)) {
				if (prepend === false) {
					getModule().historyBlocked = false;
				}
				return;
			}

			const messageDate = new Date();
			messageDate.setTime(messageObject.timestamp);
			const placeholderClass = 'm' + new Date().getTime();
			const placeholder = '<span class="' + placeholderClass + '"></span>';

			template = template.replace(/\[\[username\]\]/g, findUsernameInConversationByMessage(messageObject));
			template = template.replace(/\[\[time_raw\]\]/g, messageObject.timestamp);
			template = template.replace(/\[\[time\]\]/g, dateTimeFormatter.fromNowToTime(messageObject.timestamp));
			template = template.replace(/\[\[time_only\]\]/g, dateTimeFormatter.format(messageObject.timestamp, 'LT'));
			template = template.replace(/\[\[message]\]/g, placeholder);
			template = template.replace(/\[\[avatar\]\]/g, getProfileImage(messageObject.userId));
			template = template.replace(/\[\[userId\]\]/g, messageObject.userId);
			template = template.replace(/\[\[position\]\]/g, position);

			const dummyParent = document.createElement('div');
			dummyParent.innerHTML = template;
			dummyParent.querySelectorAll('.' + placeholderClass).forEach(n => {
				n.textContent = message;
			});

			const $firstHeader = chatBody.querySelector('li.header');
			const $messages = chatBody.querySelectorAll('li.message');
			const firstHeaderUsrId = $firstHeader ? $firstHeader.dataset.headerUsrId : undefined;
			let renderSeparator = false;
			let renderHeader = true;
			let insertAfterFirstHeader = false;
			let insertBeforeLastAdded = false;

			if (prepend === true) {
				let firstMessageMessageDate = new Date();
				firstMessageMessageDate.setTime($messages[0] ? $messages[0].querySelector('.iosOnScreenChatBodyMsg').getAttribute('data-message-time') : undefined);

				if (
					messageDate.getDay() !== firstMessageMessageDate.getDay() ||
					messageDate.getMonth() !== firstMessageMessageDate.getMonth() ||
					messageDate.getYear() !== firstMessageMessageDate.getYear()
				) {
					renderSeparator = true;
				} else {
					insertBeforeLastAdded = true;
					if (firstHeaderUsrId !== undefined && parseInt(firstHeaderUsrId) === parseInt(messageObject.userId)) {
						// The author of the message to be prepended is the same as the first message
						renderHeader = false;
						insertAfterFirstHeader = true;
						insertBeforeLastAdded = false;
					} else {
						/*
							The author of the message to be prepended differs from the author of the first message.
							We need to render a new header
						 */
					}
				}
 			} else {
				const lastMessageDate = new Date();
				const iosMsg = $messages[$messages.length -1] ?
                                      $messages[$messages.length -1].querySelector('.iosOnScreenChatBodyMsg') :
				      null;
				lastMessageDate.setTime(iosMsg ? iosMsg.getAttribute('data-message-time') : undefined);

				if (
					0 === $messages.length || (
						messageDate.getDay() !== lastMessageDate.getDay() ||
						messageDate.getMonth() !== lastMessageDate.getMonth() ||
						messageDate.getYear() !== lastMessageDate.getYear()
					)
				) {
					renderSeparator = true;
				}

				if (
					!renderSeparator &&
					$messages[$messages.length -1] &&
					$messages[$messages.length -1].dataset.usrId &&
					messageObject.userId == $messages[$messages.length -1].dataset.usrId
				) {
					renderHeader = false;
				}
			}

			if (messageObject.hasOwnProperty("isSystem") && messageObject.isSystem) {
				items.push(
					createNode('li', {class: position}, Array.from(dummyParent.querySelectorAll('li.system > *')).map(n => n.cloneNode(true)))
				);
			} else {
				if (renderSeparator) {
					items.push(
						createNode('li', {class: 'separator'}, Array.from(dummyParent.querySelectorAll('li.system .iosOnScreenChatBodyMsg')).map(n => {
							n.innerHTML = dateTimeFormatter.formatDate(messageObject.timestamp);
							return n;
						}))
					);
				}

				if (renderHeader) {
					items.push(createNode('li', {
						class: 'header ' + position,
						'data-header-usr-id': messageObject.userId
					}, Array.from(
						dummyParent.querySelectorAll('li.with-header.' + position)
					).map(n => n.cloneNode(true))));
				}

				items.push(
					createNode(
						'li',
						{class: 'message ' + position, 'data-usr-id': messageObject.userId},
						Array.from(dummyParent.querySelectorAll('li.message > *')).map(n => n.cloneNode(true))
					));
			}

			let $lastAdded = $firstHeader;
			(prepend === true ? items.reverse() : items).forEach(function ($template) {
				$template.classList.add('clearfix');

				if (prepend === true) {
					if (insertBeforeLastAdded) {
						$lastAdded.parentNode.insertBefore($template, $lastAdded);
						$lastAdded = $template;
					} else if (insertAfterFirstHeader) {
						if ($firstHeader.nextSibling) {
							$firstHeader.parentNode.insertBefore($template, $firstHeader.nextSibling);
						} else {
							$firstHeader.parentNode.appendChild($template);
						}
					} else {
						chatBody.prepend($template);
					}
				} else {
					chatBody.appendChild($template);
				}
			});

			chatBody.querySelectorAll('[data-onscreenchat-body-msg]').forEach(tryLink);

			if (prepend === false) {
				getModule().scrollBottom(chatWindow);
				getModule().historyBlocked = false;
			}
		},

		resizeWindow: function() {
			const width = window.outerWidth;
			const space = parseInt(width / getModule().chatWindowWidth);

			if (space != getModule().numWindows) {
				const openWindows = countOpenChatWindows();
				const diff = openWindows - space;
				getModule().numWindows = space;

				if(diff > 0) {
					for (let i = 0; i < diff; i++) {
						getModule().closeWindowWithLongestInactivity();
					}
				}
			}
		},

		closeWindowWithLongestInactivity: function(){
			var conversation = getModule().findConversationWithLongestInactivity();

			if (conversation != null) {
				conversation.action = ACTION_HIDE_CONV;
				getModule().storage.save(conversation);
			}
		},

		findConversationWithLongestInactivity: function() {
			let oldest = null;
			document.querySelectorAll('[data-onscreenchat-window]:not(.ilNoDisplay)').forEach(() => {
				const conversation = getModule().storage.get(this.dataset.onscreenchatWindow);
				if(oldest == null || oldest.lastActivity > conversation.lastActivity || conversation.lastActivity == null) {
					oldest = conversation;
				}
			});

			return oldest;
		},

		addUser: function(conversationId, userId, name) {
			$chat.addUser(conversationId, userId, name);
		},
	};

	/**
	 * @returns {window.il.OnScreenChat}
	 */
	function getModule() {
		return $scope.il.OnScreenChat;
	}

	/**
	 * @returns {window.il.OnScreenChat.config|{}}
	 */
	function getConfig() {
		return $scope.il.OnScreenChat.config;
	}

	function countOpenChatWindows() {
		return document.querySelectorAll('[data-onscreenchat-window]:not(.ilNoDisplay)').length;
	}

	const DeferredCallbackFactory = (function() {
		let namespaces = {};

		return function (ns) {
			if (!namespaces.hasOwnProperty(ns)) {
				namespaces[ns] = (function () {
					let timer = 0;

					return function(callback, ms){
						clearTimeout(timer);
						timer = setTimeout(callback, ms);
					};
				})();
			}

			return namespaces[ns];
		};
	})();

	function ConversationStorage() {
		this.get = function (id) {
			return JSON.parse(window.localStorage.getItem(PREFIX_CONSTANT + id));
		};

		this.syncUIStateWithStored = function (conversation) {
			let oldValue = this.get(conversation.id);

			if (oldValue != null && oldValue.open !== undefined && (conversation.open === undefined || conversation.open !== oldValue.open)) {
				conversation.open = oldValue.open;
			}

			if (
				oldValue != null && oldValue.latestMessage !== undefined && oldValue.latestMessage !== null &&
				(conversation.latestMessage === undefined || conversation.latestMessage === null)
			) {
				conversation.latestMessage = oldValue.latestMessage;
			}

			if (oldValue != null && oldValue.lastTriggeredNotificationTs !== undefined && (conversation.lastTriggeredNotificationTs === undefined || conversation.lastTriggeredNotificationTs < oldValue.lastTriggeredNotificationTs)) {
				conversation.lastTriggeredNotificationTs = oldValue.lastTriggeredNotificationTs;
			}

			return conversation;
		}; 

		this.save = function (conversation, callback) {
			let oldValue = this.get(conversation.id);

			conversation.messages = [];

			conversation = getModule().storage.syncUIStateWithStored(conversation);

			if (conversation.action !== undefined) {
				if (ACTION_DERIVED_FROM_CONV_OPEN_STATUS === conversation.action) {
					if (conversation.open) {
						conversation.action = ACTION_SHOW_CONV;
					} else {
						conversation.action = ACTION_HIDE_CONV; 
					}
				}

				if (ACTION_SHOW_CONV === conversation.action) {
					conversation.lastActivity = new Date().getTime();
					conversation.numNewMessages = 0;
					conversation.open = true;
				} else if (ACTION_HIDE_CONV === conversation.action || ACTION_REMOVE_CONV === conversation.action) {
					conversation.open = false;
				}
			}

			conversation.callback = callback;
			conversation.type = TYPE_CONSTANT;

			const newValue = JSON.stringify(conversation);
			window.localStorage.setItem(PREFIX_CONSTANT + conversation.id, newValue);
			const event = new StorageEvent('storage', {
				key: PREFIX_CONSTANT + conversation.id,
				oldValue: oldValue,
				newValue: newValue,
			});
			event.callback = callback;
			window.dispatchEvent(event);
		};
	};

	const DeferredActivityTrackerFactory = (function () {
		let instances = {}, ms = 1000;

		function ActivityTracker() {
			this.timer = 0;
		}

		ActivityTracker.prototype.track = function(cb) {
			clearTimeout(this.timer);
			this.timer = window.setTimeout(cb, ms);
		};

		/**
		 * 
		 * @param {String} conversationId
		 * @returns {ActivityTracker}
		 */
		function createInstance(conversationId) {
			return new ActivityTracker();
		}

		return {
			/**
			 * @param {String} conversationId
			 * @returns {ActivityTracker}
			 */
			getInstance: function (conversationId) {
				if (!instances.hasOwnProperty(conversationId)) {
					instances[conversationId] = createInstance(conversationId);
				}
				return instances[conversationId];
			}
		};
	})();

	const findUsernameByIdByConversation = function(conversation, usrId) {
		for (let index in conversation.participants) {
			if(conversation.participants.hasOwnProperty(index) && conversation.participants[index].id == usrId) {
				if (getModule().participantsNames.hasOwnProperty(conversation.participants[index].id)) {
					return getModule().participantsNames[conversation.participants[index].id];
				}

				return conversation.participants[index].name;
			}
		}

		return "";
	};

	const findUsernameInConversationByMessage = function(messageObject) {
		let conversation = getModule().storage.get(messageObject.conversationId);

		return findUsernameByIdByConversation(conversation, messageObject.userId);
	};

	const getParticipantsIds = function(conversation) {
		let ids = [];

		for (let index in conversation.participants) {
			if(conversation.participants.hasOwnProperty(index)) {
				ids.push(conversation.participants[index].id);
			}
		}

		return ids;
	};

	const getParticipantsNames = function(conversation, predicate = null) {
		let names = [];

		for (let key in conversation.participants) {
			if (
				conversation.participants.hasOwnProperty(key) && (
					null === predicate || predicate(conversation.participants[key].id)
				)
			) {
				if (getModule().participantsNames.hasOwnProperty(conversation.participants[key].id)) {
					names.push(getModule().participantsNames[conversation.participants[key].id]);
					continue;
				}

				names.push(conversation.participants[key].name);
			}
		}

		return names;
	};

	function ParticipantsTooltipFormatter(participants) {
		let _participants = participants;

		this.format = function () {
			let i = 1;

			return _participants.map(function(elm) {
				return (i++ + ". ") + elm
			}).join(" / ");
		};
	};

	const getProfileImage = function(userId) {
		if (getModule().participantsImages.hasOwnProperty(userId)) {
			return getModule().participantsImages[userId].src;
		}
		return "";
	};

	const TypingBroadcasterFactory = (function () {
		let instances = {}
		const ms = 5000;

		/**
		 * 
		 * @param {Function} onTypingStarted
		 * @param {Function} onTypeingStopped
		 * @constructor
		 */
		function TypingBroadcaster(onTypingStarted, onTypingStopped) {
			this.is_typing = false;
			this.timer = 0;
			this.onTypingStarted = onTypingStarted;
			this.onTypingStopped = onTypingStopped;
		}

		TypingBroadcaster.prototype.release = function() {
			if (this.is_typing) {
				window.clearTimeout(this.timer);
				this.onTimeout();
			}
		}

		TypingBroadcaster.prototype.onTimeout = function() {
			this.is_typing = false;
			this.onTypingStopped.call();
		};

		TypingBroadcaster.prototype.registerTyping = function() {
			if (this.is_typing) {
				window.clearTimeout(this.timer);
				this.timer = window.setTimeout(this.onTimeout.bind(this), ms);
			} else {
				this.is_typing = true;
				this.onTypingStarted.call();
				this.timer = window.setTimeout(this.onTimeout.bind(this), ms);
			}
		};

		/**
		 *
		 * @param {String} conversationId
		 * @param {Function} onTypingStarted
		 * @param {Function} onTypingStopped
		 * @returns {TypingBroadcaster}
		 */
		function createInstance(conversationId, onTypingStarted, onTypingStopped) {
			return new TypingBroadcaster(onTypingStarted, onTypingStopped);
		}

		return {
			/**
			 * @param {String} conversationId
			 * @param {Function} onTypingStarted
			 * @param {Function} onTypingStopped
			 * @returns {TypingBroadcaster}
			 */
			getInstance: function (conversationId, onTypingStarted, onTypingStopped) {
				if (!instances.hasOwnProperty(conversationId)) {
					instances[conversationId] = createInstance(conversationId, onTypingStarted, onTypingStopped);
				}

				return instances[conversationId];
			},
			releaseAll: function () {
				for (let conversationId in instances) {
					if (instances.hasOwnProperty(conversationId)) {
						instances[conversationId].release();
					}
				}
			}
		};
	})();

	const TypingUsersTextGeneratorFactory = (function () {
		let instances = {};

		/**
		 *
		 * @param {String} conversationId
		 * @constructor
		 */
		function TypingUsersTextGenerator(conversationId) {
			this.conversationId = conversationId;
			this.typingSet = new Set();
		}

		/**
		 *
		 * @param {Number} usrId
		 */
		TypingUsersTextGenerator.prototype.addTypingUser = function(usrId) {
			if (!this.typingSet.has(usrId)) {
				this.typingSet.add(usrId);
			}
		}

		/**
		 * 
		 * @param {Number} usrId
		 */
		TypingUsersTextGenerator.prototype.removeTypingUser = function(usrId) {
			if (this.typingSet.has(usrId)) {
				this.typingSet.delete(usrId);
			}
		};

		/**
		 * 
		 * @param {ConversationStorage} storage
		 * @param {il.Language} language
		 * @param {Function} usernameGenerator
		 * @returns {string}
		 */
		TypingUsersTextGenerator.prototype.text = function (storage, language, usernameGenerator) {
			const names = usernameGenerator(
				storage.get(this.conversationId),
				function(usrId) {
					return this.typingSet.has(usrId);
				}.bind(this)
			);

			if (names.length === 0) {
				return '';
			} else if (1 === names.length) {
				return language.txt("chat_user_x_is_typing", names[0]);
			}

			return language.txt("chat_users_are_typing");
		};

		/**
		 *
		 * @param {String} conversationId
		 * @returns {TypingUsersTextGenerator}
		 */
		function createInstance(conversationId) {
			return new TypingUsersTextGenerator(conversationId);
		}

		return {
			/**
			 * @param {String} conversationId
			 * @returns {TypingUsersTextGenerator}
			 */
			getInstance: function (conversationId) {
				if (!instances.hasOwnProperty(conversationId)) {
					instances[conversationId] = createInstance(conversationId);
				}
				return instances[conversationId];
			}
		};
	})();

    function insertAtCursor(node, text){
        const lastCaretPosition = node.selectionStart;
        node.value = node.value.substr(0, lastCaretPosition) + text + node.value.substr(lastCaretPosition);
        const newCursorPos = lastCaretPosition + text.length;
        node.setSelectionRange(newCursorPos, newCursorPos);
        node.focus();
    }

    function mapObject(obj, proc){
        return Object.fromEntries(Object.entries(obj).map(function(entry){
            return [entry[0], proc(entry[1], entry[0])];
        }));
    }
    function piecesOf(nr, array){
        let current = array;
        const result = [];
        while(current.length) {
            result.push(current.slice(0, 2));
            current = current.slice(nr);
        }
        return result;
    }

	function lazy(proc){
		let call = () => {
			const value = proc();
			call = () => value;
			return value;
		};
		return () => call();
	}

	function cache(proc){
		const cached = {};
		return (...args) => {
			const key = JSON.stringify(args);
			if(!cached[key]){
				cached[key] = proc(...args);
			}
			return cached[key];
		};
	}
})(
  // Currently jQuery is required for UI events to work:
  // s => document.dispatchEvent(new CustomEvent(s)) doesn't work
  // This can be changed when the UI components don't use jQuery for event dispatching.
  s => $(document).trigger(s, {}),
  window,
  window.il.Chat,
  window.il.ChatDateTimeFormatter
);
