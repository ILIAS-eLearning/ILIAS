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

(function($, $scope, $chat, dateTimeFormatter){
	'use strict';

	var TYPE_CONSTANT	= 'osc';
	var PREFIX_CONSTANT	= TYPE_CONSTANT + '_';
	var ACTION_SHOW_CONV = "show";
	var ACTION_HIDE_CONV = "hide";
	var ACTION_REMOVE_CONV = "remove";
	var ACTION_STORE_CONV = "store";
	var ACTION_DERIVED_FROM_CONV_OPEN_STATUS = "derivefromopen";
	const resizeTextareas = {}; // string: function
	const MAX_CHAT_LINES = 3;

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
		triggers: mapObject(triggerMap, function(){return function(){};}),

		setTriggers: function(triggers) {
			Object.keys(triggerMap).map(function(key){
				if (triggers.hasOwnProperty(key)) {
					$scope.il.OnScreenChatJQueryTriggers.triggers[key] = triggers[key];
				}
			});

			return this;
		},

		init: function() {
			$(window).on('resize', $scope.il.OnScreenChat.resizeWindow).resize();

			const body = $('body');
			mapObject(triggerMap, function(eventAndSelector, key){
				piecesOf(2, eventAndSelector).forEach(function(eventAndSelector){
					body.on(eventAndSelector[0], eventAndSelector[1], $scope.il.OnScreenChatJQueryTriggers.triggers[key]);
				});
			});
		}
	};

	$scope.il.OnScreenChat = {
		config: {},
		container: $('<div></div>').addClass('iosOnScreenChat'),
		storage: undefined,
		user: undefined,
		historyBlocked: false,
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

		setConfig: function(config) {
			getModule().config = config;
			dateTimeFormatter.setLocale(config.locale);
		},

		init: function() {
			getModule().storage   = new ConversationStorage();

			const loadModal = busName => il.Chatroom.sendFromURL(getModule().config.modalURLTemplate)(busName).then(r => r.text()).then(modalHTML => {
				const modal = $(modalHTML);
				$(document.body).append(modal);
				return new Promise(resolve => getModule().bus.onArrived(busName, ([showSignal, closeSignal]) => resolve({
					showModal: () => $(document).trigger(showSignal, {}),
					closeModal: () => $(document).trigger(closeSignal, {}),
					node: modal[0],
				})))
			});

			const confirmModal = lazy(() => {
				let currentThen = null;
				const modal = loadModal('confirmRemove');
				modal.then(({node, closeModal}) => node.querySelector('form').addEventListener('submit', e => {
					e.preventDefault();
					closeModal();
					currentThen();
				}));

				return then => {
					currentThen = then;
					modal.then(({showModal}) => showModal());
				}
			});

			const inviteModal = lazy(() => {
				let currentConversationId = null;
				const setup = loadModal('inviteModal').then(modalInfo => il.Chatroom.inviteUserToRoom(
					modalInfo,
					{
						more: '»' + il.Language.txt('autocomplete_more'),
						nothingFound: $(getModule().config.nothingFoundTemplate)[0],
					},
					entry => getModule().addUser(currentConversationId, entry.id, entry.value),
					((getModule().storage.get(currentConversationId) || {}).participants || []).map(p => p.id),
					({search, all}) => il.Chatroom.sendFromURL(getModule().config.userListURL)(
						'',
						Object.assign({term: search}, all ? {fetchall: '1'} : {})
					).then(r => r.json())
				));

				return conversationId => {
					currentConversationId = conversationId;
					setup.then(open => open());
				};
			});

			getModule().openConfirmModal = then => confirmModal()(then);
			getModule().openInviteUserModal = conversationId => inviteModal()(conversationId);

			$.each(getModule().config.initialUserData, function(usrId, item) {
				getModule().participantsNames[usrId] = item.public_name;

				var img = new Image();
				img.src = item.profile_image;
				getModule().participantsImages[usrId] = img;
			});

			$(window).on('storage', function(e) {
				if (
					typeof e.originalEvent.key !== "string" ||
					e.originalEvent.key.indexOf(PREFIX_CONSTANT) !== 0
				) {
					console.log("Ignored local storage event not being in namespace: " + PREFIX_CONSTANT);
					return;
				}

				var conversation = e.originalEvent.newValue;

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

					if ($.isFunction(conversation.callback)) {
						conversation.callback();
					}
				}
			});

			setInterval(() => {
				$.ajax(
					getConfig().verifyLoginURL
				).done(result => {
					result = JSON.parse(result);
					if (!result.loggedIn) {
						window.location = './login.php';
					}
				}).fail(e => {
					window.location = './login.php';
				});
			}, 300000);

			setInterval(() => {
				$('[data-livestamp]').each(() => {
					let $this = $(this);
					$this.html(dateTimeFormatter.fromNowToTime($this.data("livestamp")));
				});
				$('[data-message-time]').each(() => {
					let $this = $(this);
					$this.attr("title", dateTimeFormatter.format($this.data("message-time", "LT")));
				});
			}, 60000);

			$chat.init(getConfig().userId, getConfig().username, getModule().onLogin, getModule().onUnload);
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

			$('body').append(
				$('<div></div>')
					.attr('id', 'onscreenchat-container')
					.append(getModule().container)

			);
		},

		/**
		 * Called if a 'Start a Conversation' UI element is clicked by a conversation initiator
		 * @param e
		 */
		startConversation: function(e) {
			e.preventDefault();
			e.stopPropagation();

			let link = $(this),
				conversationId = $(link).attr('data-onscreenchat-conversation');

			if (!conversationId && this.closest('[data-id]') !== null) {
				conversationId = this.closest('[data-id]').dataset.id;
			}

			let conversation = getModule().storage.get(conversationId);

			if (conversation == null) {
				let participant = {
					id: $(link).attr('data-onscreenchat-userid'),
					name: $(link).attr('data-onscreenchat-username')
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
			let conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']'),
				newDomElementsCreated = false;

			if (conversationWindow.is(':visible')) {
				return;
			}

			if (conversationWindow.length === 0) {
				conversationWindow = $(getModule().createWindow(conversation));
				conversationWindow.find('.panel-body')
					.on("dblclick", function() {
						$(this).trigger("scroll");
					}).
					scroll(getModule().onScroll);
				getModule().container.append(conversationWindow);
				getModule().addMessagesOnOpen(conversation);

				newDomElementsCreated = true;
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

			conversationWindow.find("[aria-live]").attr("aria-live", "polite")
			conversationWindow.show();

			if(countOpenChatWindows() > getModule().numWindows) {
				getModule().closeWindowWithLongestInactivity();
			}

			resizeTextareas[conversation.id] = il.Chatroom.expandableTextarea(
				'.panel-footer-for-shadow',
				'[data-onscreenchat-window="' + conversation.id + '"] [data-onscreenchat-message]',
				MAX_CHAT_LINES
			);

			getModule().resizeMessageInput.call($(conversationWindow).find('[data-onscreenchat-message]'));
			getModule().scrollBottom(conversationWindow);

		},

		scrollBottom: function(chatWindow) {
			// Prevented issue with non existing elements (when there is no conv. on document ready)
			if ($(chatWindow).find('[data-onscreenchat-body]').length > 0) {
				$(chatWindow).find('.panel-body').animate({
					scrollTop: $(chatWindow).find('[data-onscreenchat-body]')[0].scrollHeight
				}, 0);
			}
		},

		resizeMessageInput: function(e){
			var inputWrapper = $(this).closest('.panel-footer');
			var parent = $(inputWrapper).closest('[data-onscreenchat-window]');
			resizeTextareas[parent.data('onscreenchat-window')]();
			var wrapperHeight = parent.outerHeight();
			var headingHeight = parent.find('.panel-heading').outerHeight();
			var inputHeight = $(inputWrapper).outerHeight();
			var bodyHeight = wrapperHeight - inputHeight - headingHeight;

			if($(this).html() === "<br>") {
				$(this).html("");
			}

			parent.find('.panel-body').css('height', bodyHeight + "px");
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

			var $template = $(template);

			$template.find('[href="addUser"]').attr({
				"title":                 il.Language.txt('chat_osc_add_user'),
				"data-onscreenchat-add": conversation.id,
				"data-toggle":           "tooltip",
				"data-placement":        "auto"
			});
			$template.find('.minimize').attr({
				"title":                   il.Language.txt('chat_osc_minimize'),
				"data-onscreenchat-minimize": conversation.id,
				"data-toggle":             "tooltip",
				"data-placement":          "auto"
			});

			return $template;
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
						$(getModule().menuCollector)
							.off('click', '[data-id]')
							.off('click', '[data-id] .close');
						$(getModule().menuCollector)
							.on('click', '[data-id]', $scope.il.OnScreenChatJQueryTriggers.triggers.menuItemClicked)
							.on('click', '[data-id] .close', $scope.il.OnScreenChatJQueryTriggers.triggers.menuItemRemovalRequest);
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
			const conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

			conversationWindow.find("[aria-live]").attr("aria-live", "off")
			conversationWindow.hide();

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
			const conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

			conversationWindow.find("[aria-live]").attr("aria-live", "off")
			conversationWindow.hide();

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

			var conversation = getModule().storage.get($(this).attr('data-onscreenchat-minimize'));

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
				getModule().resizeMessageInput.call($(input));
				e.preventDefault();
				return false;
			} else if (isEnter || e.type === 'click') {
				e.preventDefault();
				const conversationId = $(this).closest('[data-onscreenchat-window]').attr('data-onscreenchat-window');
				getModule().send(conversationId);
				getModule().historyBlocked = true;
			}
		},

		send: function(conversationId) {
			var input = $('[data-onscreenchat-window=' + conversationId + ']').find('[data-onscreenchat-message]');
			var message = input.val();

			if(message !== "") {
				$chat.sendMessage(conversationId, message);
				input.val('');
				getModule().onMessageInput.call(input);
				getModule().resizeMessageInput.call(input);

				var e = $.Event('click');
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
				getModule().storage.save(conversation, function() {
					getModule().addMessage(conversation, messageObject, false);
				});

				if (
					(!messageObject.hasOwnProperty("isSystem") || !messageObject.isSystem) &&
					getModule().user !== undefined &&
					getConfig().enabledBrowserNotifications &&
					parseInt(getModule().user.id) !== parseInt(messageObject.userId)
				) {
					il.OnScreenChatNotifications.send(
						messageObject.id,
						conversation.id,
						il.Language.txt('osc_noti_title'),
						$("<span>").html(messageObject.message).text(),
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
							/%s/ig, $.map(ignoredParticipants, function(val) {
								var name = findUsernameByIdByConversation(conversation, val);

								return name ? name : null;
							}).join(', ')
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

			const conversationId = $(this).closest('[data-onscreenchat-window]').attr('data-onscreenchat-window');
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

			const input = $('[data-onscreenchat-window=' + conversationId + ']').find('[data-onscreenchat-message]');
			if (input.val().trim() === "") {
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
			const container = $('[data-onscreenchat-window=' + conversation.id + ']');

			container.find('[data-onscreenchat-typing]').text(text);
		},

		/**
		 * 
		 * @param conversation
		 * @returns {jQuery.Deferred}
		 */
		requestUserProfileData: function(conversation) {
			var dfd = new $.Deferred(),
				participantsIds = getParticipantsIds(conversation);

			participantsIds = participantsIds.filter(function(id){
				return !getModule().participantsImages.hasOwnProperty(id);
			});

			if (participantsIds.length === 0) {
				dfd.resolve();

				return dfd;
			}

			$.ajax({
				url: getModule().config.userProfileDataURL + '&usr_ids=' + participantsIds.join(','),
				dataType: 'json',
				method: 'GET'
			}).done(function(response) {
				$.each(response, function(id, item){
					getModule().participantsNames[id] = item.public_name;

					var img = new Image();
					img.src = item.profile_image;
					getModule().participantsImages[id] = img;

					$('[data-onscreenchat-avatar='+id+']').attr('src', img.src);
				});

				dfd.resolve();
			});

			return dfd;
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
			conversation.lastActivity = (new Date).getTime();
			getModule().storage.save(conversation);

			$
				.when(getModule().requestUserProfileData(conversation))
				.then(function() {

					conversation.action = ACTION_SHOW_CONV;
					getModule().storage.save(conversation);
				});
		},

		onMenuItemRemovalRequest: function(e) {
			e.preventDefault();
			e.stopPropagation();

			let $trigger = $(this), conversationId = $trigger.data('onscreenchat-conversation');

			if (!conversationId) {
				conversationId = $trigger.closest('[data-onscreenchat-conversation]').data('onscreenchat-conversation');
			}

			if (!conversationId) {
				conversationId = this.closest('[data-id]').dataset.id;
			}

			if (!conversationId) {
				return;
			}

			let conversation = getModule().storage.get(conversationId);
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
				$(e.target).closest('[data-onscreenchat-header]').length === 0 &&
				$(e.target).parent('[data-onscreenchat-body-msg]').length === 0
			) {
				e.preventDefault();
				e.stopPropagation();

				$(this).find('[data-onscreenchat-message]').focus();
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
			var conversation = getModule().storage.get($(this).attr('data-onscreenchat-window'));
			getModule().trackActivityFor(conversation);
		},

		onConversation: function(conversation) {
			// Directly save the conversation on storage to prevent race conditions
			conversation.action = ACTION_STORE_CONV;
			getModule().storage.save(conversation);

			var chatWindow = $('[data-onscreenchat-window='+conversation.id+']');

			$
				.when(getModule().requestUserProfileData(conversation))
				.then(function() {
					if (chatWindow.length !== 0) {
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

						chatWindow
							.find('[data-onscreenchat-window-participants]')
							.html(header)
							.attr("title", tooltip)
							.attr("data-original-title", tooltip);
					}

					conversation.action = ACTION_DERIVED_FROM_CONV_OPEN_STATUS;
					getModule().storage.save(conversation);
			});
		},

		onHistory: function (conversation) {
			let container = $('[data-onscreenchat-window=' + conversation.id + ']'),
				messages = Object.values(conversation.messages),
				messagesHeight = container.find('[data-onscreenchat-body]').outerHeight();

			messages.forEach(function(message) {
				getModule().addMessage(conversation, message, !conversation.reverseSorting);
			});

			if (
				undefined === getModule().historyTimestamps[conversation.id] ||
				conversation.oldestMessageTimestamp < getModule().historyTimestamps[conversation.id]
			) {
				let newMessagesHeight = container.find('[data-onscreenchat-body]').outerHeight();
				container.find('.panel-body').scrollTop(newMessagesHeight - messagesHeight);
				getModule().historyTimestamps[conversation.id] = conversation.oldestMessageTimestamp;
			}

			getModule().historyBlocked = false;

			container.find('.ilOnScreenChatMenuLoader').closest('div').remove();
		},

		onScroll: function() {
			let container = $(this).closest('[data-onscreenchat-window]'),
				conversation = getModule().storage.get(container.attr('data-onscreenchat-window'));

			if ($(this).scrollTop() === 0 && !getModule().historyBlocked && conversation.latestMessage != null) {
				getModule().historyBlocked = true;
				$(this).prepend(
					$('<div></div>').css('text-align', 'center').css('margin-top', '-10px').append(
						$('<img />').addClass("ilOnScreenChatMenuLoader").attr('src', getConfig().loaderImg)
					)
				);

				let oldestMessageTimestamp = getModule().historyTimestamps[conversation.id];
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
			conversation.lastActivity = (new Date()).getTime();
			getModule().storage.save(conversation);

			DeferredActivityTrackerFactory.getInstance(conversation.id).track(function() {
				$chat.trackActivity(conversation.id, getModule().user.id, conversation.lastActivity);
			});
		},

		getCaretPosition: function(elm) {
			var caretPos = 0,
				sel, range;

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
					var tempEl = document.createElement("span");
					elm.insertBefore(tempEl, elm.firstChild);
					var tempRange = range.duplicate();
					tempRange.moveToElementText(tempEl);
					tempRange.setEndPoint("EndToEnd", range);
					caretPos = tempRange.text.length;
				}
			}
			return caretPos;
		},

		onMessageInput: function() {
			var $this = $(this);

			$this.attr("data-onscreenchat-last-caret-pos", getModule().getCaretPosition($this.get(0)));
		},

		shouldPrintMessage: function (conversation, messageObject, prepend) {
			let doPrintMessage = true,
				username = findUsernameInConversationByMessage(messageObject);

			if (username === "") {
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
			let template = getModule().config.messageTemplate,
				position = (messageObject.userId == getModule().config.userId)? 'right' : 'left',
				message = messageObject.message.replace(/(?:\r\n|\r|\n)/g, '<br />'),
				chatWindow = $('[data-onscreenchat-window=' + messageObject.conversationId + ']'),
				chatBody = chatWindow.find("[data-onscreenchat-body]"),
				items = [];

			if (!getModule().shouldPrintMessage(conversation, messageObject, prepend)) {
				if (prepend === false) {
					getModule().historyBlocked = false;
				}
				return;
			}

			let messageDate = new Date();
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

			template = $(template);
			template.find('.' + placeholderClass).each(function(){
				this.textContent = message;
			});

			let $firstHeader = chatBody.find("li.header").first(),
				$messages = chatBody.find("li.message"),
				firstHeaderUsrId = $firstHeader.data("header-usr-id"),
				renderSeparator = false,
				renderHeader = true,
				insertAfterFirstHeader = false,
				insertBeforeLastAdded = false;

			if (prepend === true) {
				let firstMessageMessageDate = new Date();
				firstMessageMessageDate.setTime($messages.first().find(".iosOnScreenChatBodyMsg").attr("data-message-time"));

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
				let lastMessageDate = new Date();
				lastMessageDate.setTime($messages.last().find(".iosOnScreenChatBodyMsg").attr("data-message-time"));

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
					$messages.last().data("usr-id") &&
					messageObject.userId == $messages.last().data("usr-id")
				) {
					renderHeader = false;
				}
			}

			if (messageObject.hasOwnProperty("isSystem") && messageObject.isSystem) {
				items.push(
					$("<li></li>").append(
							$(template).find("li.system").html()
						)
						.addClass(position)
				);
			} else {
				if (renderSeparator) {
					items.push(
						$("<li></li>").append(
								$(template).find("li.system").find(".iosOnScreenChatBodyMsg").html(
									dateTimeFormatter.formatDate(messageObject.timestamp)
								)
							)
							.addClass("separator")
					);
				}

				if (renderHeader) {
					items.push($("<li></li>").append(
							$(template).find("li.with-header." + position).html()
						)
						.addClass("header " + position)
						.data("header-usr-id", messageObject.userId));
				}

				items.push(
					$("<li></li>").append(
							$(template).find("li.message").html()
						)
						.addClass("message " + position)
						.data("usr-id", messageObject.userId)
				);
			}

			if (prepend === true) {
				items = items.reverse();
			}

			let $lastAdded = $firstHeader;
			items.forEach(function ($template) {
				$template.addClass("clearfix");

				if (prepend === true) {
					if (insertBeforeLastAdded) {
						$template.insertBefore($lastAdded);
						$lastAdded = $template;
					} else if (insertAfterFirstHeader) {
						$template.insertAfter($firstHeader);
					} else {
						chatBody.prepend($template);
					}
				} else {
					chatBody.append($template);
				}
			});

			tryLink(chatBody.find('[data-onscreenchat-body-msg]'));

			if (prepend === false) {
				getModule().scrollBottom(chatWindow);
				getModule().historyBlocked = false;
			}
		},

		resizeWindow: function() {
			let width = $(this).outerWidth(),
				space = parseInt(width / getModule().chatWindowWidth);

			if (space != getModule().numWindows) {
				let openWindows = countOpenChatWindows(),
					diff = openWindows - space;
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
			var oldest = null;
			$('[data-onscreenchat-window]:visible').each(function(){
				var conversation = getModule().storage.get($(this).data('onscreenchat-window'));
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
		return $('[data-onscreenchat-window]:visible').length;
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

	const ConversationStorage = function ConversationStorage() {

		this.get = function get(id) {
			return JSON.parse(window.localStorage.getItem(PREFIX_CONSTANT + id));
		};

		this.syncUIStateWithStored = function mergeWithStored(conversation) {
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

		this.save = function save(conversation, callback) {
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
					conversation.lastActivity = (new Date).getTime();
					conversation.numNewMessages = 0;
					conversation.open = true;
				} else if (ACTION_HIDE_CONV === conversation.action || ACTION_REMOVE_CONV === conversation.action) {
					conversation.open = false;
				}
			}

			conversation.callback	= callback;
			conversation.type		= TYPE_CONSTANT;

			window.localStorage.setItem(PREFIX_CONSTANT + conversation.id, JSON.stringify(conversation));

			let e = $.Event('storage');
			e.originalEvent = {
				key: PREFIX_CONSTANT + conversation.id,
				oldValue: oldValue,
				newValue: conversation
			};
			$(window).trigger(e);
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

	const ParticipantsTooltipFormatter = function ParticipantsTooltipFormatter(participants) {
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
		let instances = {}, ms = 5000;

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
})(jQuery, window, window.il.Chat, window.il.ChatDateTimeFormatter);
