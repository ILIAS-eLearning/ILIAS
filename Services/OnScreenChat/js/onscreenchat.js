(function($, $scope, $chat, $menu){
	'use strict';

	var TYPE_CONSTANT	= 'osc';
	var PREFIX_CONSTANT	= TYPE_CONSTANT + '_';
	var ACTION_SHOW_CONV = "show";
	var ACTION_HIDE_CONV = "hide";
	var ACTION_REMOVE_CONV = "remove";
	var ACTION_STORE_CONV = "store";
	var ACTION_DERIVED_FROM_CONV_OPEN_STATUS = "derivefromopen";

	$.widget( "custom.iloscautocomplete", $.ui.autocomplete, {
		more: false,
		_renderMenu: function(ul, items) {
			var that = this;
			$.each(items, function(index, item) {
				that._renderItemData(ul, item);
			});

			that.options.requestUrl = that.options.requestUrl.replace(/&fetchall=1/g, '');

			if (that.more) {
				ul.append("<li class='ui-menu-category ui-menu-more ui-state-disabled'><span>&raquo;" + il.Language.txt("autocomplete_more") + "</span></li>");
				ul.find('li').last().on('click', function(e) {
					that.options.requestUrl += '&fetchall=1';
					that.close(e);
					that.search(null, e);
					e.preventDefault();
				});
			}
		}
	});

	$scope.il.OnScreenChatJQueryTriggers = {
		triggers: {
			participantEvent: function(){},
			onEmitCloseConversation: function(){},
			submitEvent: function(){},
			addEvent: function(){},
			resizeChatWindow: function() {},
			focusOut: function() {},
			messageInput: function() {},
			menuItemRemovalRequest: function() {},
			emoticonClicked: function() {},
			messageContentPasted: function() {},
			windowClicked: function() {},
			menuItemClicked: function() {},
			updatePlaceholder: function() {}
		},

		setTriggers: function(triggers) {
			if (triggers.hasOwnProperty('participantEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent = triggers.participantEvent;
			}
			if (triggers.hasOwnProperty('onEmitCloseConversation')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.onEmitCloseConversation = triggers.onEmitCloseConversation;
			}
			if (triggers.hasOwnProperty('submitEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent = triggers.submitEvent;
			}
			if (triggers.hasOwnProperty('addEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.addEvent = triggers.addEvent;
			}
			if (triggers.hasOwnProperty('resizeChatWindow')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.resizeChatWindow = triggers.resizeChatWindow;
			}
			if (triggers.hasOwnProperty('focusOut')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.focusOut = triggers.focusOut;
			}
			if (triggers.hasOwnProperty('messageInput')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.messageInput = triggers.messageInput;
			}
			if (triggers.hasOwnProperty('menuItemRemovalRequest')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.menuItemRemovalRequest = triggers.menuItemRemovalRequest;
			}
			if (triggers.hasOwnProperty('emoticonClicked')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.emoticonClicked = triggers.emoticonClicked;
			}
			if (triggers.hasOwnProperty('messageContentPasted')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.messageContentPasted = triggers.messageContentPasted;
			}
			if (triggers.hasOwnProperty('windowClicked')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.windowClicked = triggers.windowClicked;
			}
			if (triggers.hasOwnProperty('menuItemClicked')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.menuItemClicked = triggers.menuItemClicked;
			}
			if (triggers.hasOwnProperty('updatePlaceholder')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.updatePlaceholder = triggers.updatePlaceholder;
			}

			return this;
		},

		init: function() {
			$(window).on('resize', $scope.il.OnScreenChat.resizeWindow).resize();

			$('body')
				.on('click', '[data-onscreenchat-userid]', $scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent)
				.on('click', '[data-onscreenchat-close]', $scope.il.OnScreenChatJQueryTriggers.triggers.onEmitCloseConversation)
				.on('click', '[data-action="onscreenchat-submit"]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)
				.on('click', '[data-onscreenchat-add]', $scope.il.OnScreenChatJQueryTriggers.triggers.addEvent)
				.on('click', '[data-onscreenchat-menu-item]', $scope.il.OnScreenChatJQueryTriggers.triggers.menuItemClicked)
				.on('click', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.windowClicked)
				.on('keydown', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)
				.on('input', '[data-onscreenchat-message]', function(e) {
					$scope.il.OnScreenChatJQueryTriggers.triggers.resizeChatWindow.call(this, e);
					$scope.il.OnScreenChatJQueryTriggers.triggers.updatePlaceholder.call(this, e);
				})
				.on('paste', '[data-onscreenchat-message]', $scope.il.OnScreenChatJQueryTriggers.triggers.messageContentPasted)
				.on('keyup click', '[data-onscreenchat-message]', $scope.il.OnScreenChatJQueryTriggers.triggers.messageInput)
				.on('focusout', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.focusOut)
				.on('click', '[data-onscreenchat-emoticon]', $scope.il.OnScreenChatJQueryTriggers.triggers.emoticonClicked)
				.on('click', '[data-onscreenchat-menu-remove-conversation]', $scope.il.OnScreenChatJQueryTriggers.triggers.menuItemRemovalRequest);
		}
	};

	$scope.il.OnScreenChat = {
		config: {},
		container: $('<div></div>').addClass('row').addClass('iosOnScreenChat'),
		storage: undefined,
		user: undefined,
		historyBlocked: false,
		inputHeight: undefined,
		historyTimestamps: {},
		emoticons: {},
		messageFormatter: {},
		participantsImages: {},
		participantsNames: {},
		chatWindowWidth: 278,
		numWindows: Infinity,

		setConfig: function(config) {
			getModule().config = config;

			moment.locale(config.locale);
			window.setInterval(function() {
				$('[data-livestamp]').each(function() {
					var $this = $(this);
					$this.html(momentFromNowToTime($this.data('livestamp')));
				});
			}, 60000);
		},

		init: function() {
			getModule().storage   = new ConversationStorage();
			getModule().emoticons = new Smileys(getModule().config.emoticons);
			getModule().messageFormatter = new MessageFormatter(getModule().getEmoticons());

			$menu.setMessageFormatter(getModule().getMessageFormatter());

			$.each(getModule().config.initialUserData, function(usrId, item) {
				getModule().participantsNames[usrId] = item.public_name;

				var img = new Image();
				img.src = item.profile_image;
				getModule().participantsImages[usrId] = img;
			});
			$menu.syncPublicNames(getModule().participantsNames);
			$menu.syncProfileImages(getModule().participantsImages);

			$(window).on('storage', function(e) {
				if (
					typeof e.originalEvent.key !== "string" ||
					e.originalEvent.key.indexOf(PREFIX_CONSTANT) !== 0
				) {
					console.log("Ignored storage event not being in namespace: " + PREFIX_CONSTANT);
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

			setInterval(function(){
				$.ajax(
					getConfig().verifyLoginURL
				).done(function(result) {
					result = JSON.parse(result);
					if(!result.loggedIn) {
						window.location = './login.php';
					}
				}).fail(function(e){
					window.location = './login.php';
				});
			}, 300000); // 5 minutes

			$chat.init(getConfig().userId, getConfig().username, getModule().onLogin);
			$chat.receiveMessage(getModule().receiveMessage);
			$chat.onParticipantsSuppressedMessages(getModule().onParticipantsSuppressedMessages);
			$chat.onSenderSuppressesMessages(getModule().onSenderSuppressesMessages);
			$chat.receiveConversation(getModule().onConversation);
			$chat.onHistory(getModule().onHistory);
			$chat.onGroupConversation(getModule().onConversationInit);
			$chat.onGroupConversationLeft(getModule().onConversationLeft);
			$chat.onConversationInit(getModule().onConversationInit);

			$scope.il.OnScreenChatJQueryTriggers.setTriggers({
				participantEvent:        getModule().startConversation,
				onEmitCloseConversation: getModule().onEmitCloseConversation,
				submitEvent:             getModule().handleSubmit,
				addEvent:                getModule().openInviteUser,
				resizeChatWindow:        getModule().resizeMessageInput,
				focusOut:                getModule().onFocusOut,
				messageInput:            getModule().onMessageInput,
				menuItemRemovalRequest:  getModule().onMenuItemRemovalRequest,
				emoticonClicked:         getModule().onEmoticonClicked,
				messageContentPasted:    getModule().onMessageContentPasted,
				windowClicked:           getModule().onWindowClicked,
				menuItemClicked:         getModule().onMenuItemClicked,
				updatePlaceholder:       getModule().updatePlaceholder
			}).init();

			$('body').append(
				$('<div></div>')
					.attr('id', 'onscreenchat-container')
					.addClass('container')
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

			var link = $(this);
			var conversationId = $(link).attr('data-onscreenchat-conversation');
			var participant = {
				id: $(link).attr('data-onscreenchat-userid'),
				name: $(link).attr('data-onscreenchat-username')
			};
			var conversation = getModule().storage.get(conversationId);

			if (typeof il.Awareness !== "undefined") {
				il.Awareness.close();
			}

			if(conversation == null) {
				$chat.getConversation([getModule().user, participant]);
				return;
			}

			conversation.action = ACTION_SHOW_CONV;
			getModule().storage.save(conversation);
		},

		open: function(conversation) {
			var conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

			if (conversationWindow.is(':visible')) {
				return;
			}

			if (conversationWindow.size() === 0) {
				conversationWindow = $(getModule().createWindow(conversation));
				conversationWindow.find('.panel-body').scroll(getModule().onScroll);
				conversationWindow
					.find('[data-onscreenchat-emoticons]')
					.append(getModule().getEmoticons().getTriggerHtml())
					.find('.iosOnScreenChatEmoticonsPanel')
					.parent()
					.removeClass('ilNoDisplay');
				getModule().container.append(conversationWindow);
				getModule().addMessagesOnOpen(conversation);

				conversationWindow.find('[data-toggle="tooltip"]').tooltip({
					container: 'body',
					viewport: { selector: 'body', padding: 10 }
				});
				conversationWindow.find('[data-toggle="participants-tooltip"]').tooltip({
					container: 'body',
					viewport: { selector: 'body', padding: 10 },
					template: '<div class="tooltip ilOnScreenChatWindowHeaderTooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
				});

				let emoticonPanel = conversationWindow.find('[data-onscreenchat-emoticons-panel]'),
					messageField = conversationWindow.find('[data-onscreenchat-message]');

				emoticonPanel.find('[data-onscreenchat-emoticons-flyout-trigger]').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					emoticonPanel.data('emoticons').preload().then(function() {
						messageField.popover({
							html:      true,
							trigger:   'manual',
							placement: 'auto',
							title:     il.Language.txt('chat_osc_emoticons'),
							content:   function () {
								return emoticonPanel.data('emoticons').getContent();
							}
						});

						messageField.popover('show');
					});
				}).on('clickoutside', function(e) {
					e.preventDefault();
					e.stopPropagation();

					messageField.popover('hide');
				});
			}

			if(conversation.latestMessage != null) {
				$chat.getHistory(conversation.id, getModule().historyTimestamps[conversation.id]);
			}

			conversationWindow.show();

			if(countOpenChatWindows() > getModule().numWindows) {
				getModule().closeWindowWithLongestInactivity();
			}

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
				var participantsNames = getParticipantsNames(conversation, false);
				var partTooltipFormatter = new ParticipantsTooltipFormatter(participantsNames);
				template = template.replace(/\[\[participants-tt\]\]/g, partTooltipFormatter.format());
				template = template.replace(
					/\[\[participants-header\]\]/g,
					il.Language.txt('chat_osc_head_grp_x_persons', participantsNames.length)
				);
			} else {
				var participantsNames = getParticipantsNames(conversation);

				template = template.replace(/\[\[participants-tt\]\]/g, participantsNames.join(', '));
				template = template.replace(/\[\[participants-header\]\]/g, participantsNames.join(', '));
			}
			template = template.replace(/\[\[conversationId\]\]/g, conversation.id);
			template = template.replace('#:#close#:#', il.Language.txt('close'));
			template = template.replace('#:#chat_osc_write_a_msg#:#', il.Language.txt('chat_osc_write_a_msg'));

			var $template = $(template);

			$template.find('[href="addUser"]').attr({
				"title":                 il.Language.txt('chat_osc_add_user'),
				"data-onscreenchat-add": conversation.id,
				"data-toggle":           "tooltip",
				"data-placement":        "auto"
			});
			$template.find('.close').attr({
				"title":                   il.Language.txt('close'),
				"data-onscreenchat-close": conversation.id,
				"data-toggle":             "tooltip",
				"data-placement":          "auto"
			});

			return $template;
		},

		/**
		 * Is called (for each browser tab) if an 'Conversation Remove' action was emitted as LocalStorage event
		 * @param conversation
		 */
		onRemoveConversation: function(conversation) {
			$('[data-onscreenchat-window=' + conversation.id + ']').hide();
			$menu.remove(conversation);
		},

		/**
		 * Is called (for each browser tab) if an 'Conversation Close' action was emitted as LocalStorage event
		 * @param conversation
		 */
		onCloseConversation: function(conversation) {
			$('[data-onscreenchat-window=' + conversation.id + ']').hide();
			$menu.addOrUpdate(conversation);
		},

		/**
		 * Is called (for each browser tab) if an 'Conversation Open' action was emitted as LocalStorage event
		 * @param conversation
		 */
		onOpenConversation: function(conversation) {
			getModule().open(conversation);
			$menu.addOrUpdate(conversation);
		},

		/**
		 * Triggered if a conversation window should be closed by an UI event in ONE tab
		 * Triggers itself a localStorage event, which results in a call to onCloseConversation for ALL browser tabs
		 * @param e
		 */
		onEmitCloseConversation: function(e) {
			e.preventDefault();
			e.stopPropagation();

			var conversation = getModule().storage.get($(this).attr('data-onscreenchat-close'));

			conversation.action = ACTION_HIDE_CONV;
			getModule().storage.save(conversation);
		},

		handleSubmit: function(e) {
			if ((e.keyCode === 13 && !e.shiftKey) || e.type === 'click') {
				e.preventDefault();
				var conversationId = $(this).closest('[data-onscreenchat-window]').attr('data-onscreenchat-window');
				getModule().send(conversationId);
				getModule().historyBlocked = true;
			}
		},

		send: function(conversationId) {
			var input = $('[data-onscreenchat-window=' + conversationId + ']').find('[data-onscreenchat-message]');
			var message = input.text();

			if(message !== "") {
				$chat.sendMessage(conversationId, message);
				input.html('');
				getModule().onMessageInput.call(input);
				getModule().resizeMessageInput.call(input);

				var e = $.Event('click');
				$scope.il.OnScreenChatJQueryTriggers.triggers.updatePlaceholder.call(input, e);
			}
		},

		addMessagesOnOpen: function(conversation) {
			var messages = conversation.messages;

			for(var index in messages) {
				if(messages.hasOwnProperty(index)) {
					getModule().addMessage(messages[index], false);
				}
			}
		},

		receiveMessage: function(messageObject) {
			var conversation = getModule().storage.get(messageObject.conversationId);

			if(getModule().historyTimestamps[conversation.id] === undefined) {
				getModule().historyTimestamps[conversation.id] = messageObject.timestamp;
			}

			conversation.latestMessage = messageObject;

			conversation.action = ACTION_SHOW_CONV;
			getModule().storage.save(conversation, function() {
				getModule().addMessage(messageObject, false);
			});
		},

		onParticipantsSuppressedMessages: function(messageObject) {
			messageObject.isNeutral = true;

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
			messageObject.isNeutral = true;

			messageObject.message = il.Language.txt('chat_osc_self_rej_msgs');
			getModule().receiveMessage(messageObject);
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
					$menu.syncPublicNames(getModule().participantsNames);

					var img = new Image();
					img.src = item.profile_image;
					getModule().participantsImages[id] = img;

					$('[data-onscreenchat-avatar='+id+']').attr('src', img.src);
					$menu.syncProfileImages(getModule().participantsImages);
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

			var conversationId = $(this).closest('[data-onscreenchat-conversation]').data('onscreenchat-conversation');
			var conversation = getModule().storage.get(conversationId);

			if (conversation.isGroup) {
				$scope.il.Modal.dialogue({
					id: 'modal-leave-' + conversation.id,
					header: il.Language.txt('chat_osc_leave_grp_conv'),
					body: il.Language.txt('chat_osc_sure_to_leave_grp_conv'),
					buttons:  {
						confirm: {
							type:      "button",
							label:     il.Language.txt("confirm"),

							className: "btn btn-primary",
							callback:  function (e, modal) {
								e.stopPropagation();
								modal.modal("hide");

								$chat.closeConversation(conversationId, getModule().user.id);
								$chat.removeUser(conversationId, getModule().user.id, getModule().user.name);
							}
						},
						cancel:  {
							label:     il.Language.txt("cancel"),
							type:      "button",
							className: "btn btn-default",
							callback:  function (e, modal) {
								e.stopPropagation();
								modal.modal("hide");
							}
						}
					},
					show: true
				});
			} else {
				$chat.closeConversation(conversationId, getModule().user.id);

				conversation.action = conversation.action = ACTION_REMOVE_CONV;
				getModule().storage.save(conversation);
			}
		},

		onEmoticonClicked: function(e) {
			var conversationWindow = $(this).closest('[data-onscreenchat-window]'),
				messageField = conversationWindow.find('[data-onscreenchat-message]');

			e.preventDefault();
			e.stopPropagation();
	
			var messagePaster = new MessagePaster(messageField);
			messagePaster.paste($(this).find('img').data('emoticon'));
			messageField.popover('hide');

			$scope.il.OnScreenChatJQueryTriggers.triggers.updatePlaceholder.call(messageField.get(0), e);
		},

		onMessageContentPasted: function(e) {
			var text = (e.originalEvent || e).clipboardData.getData('text/plain');

			e.stopPropagation();
			e.preventDefault();

			var messagePaster = new MessagePaster($(this));
			messagePaster.paste(text);

			$scope.il.OnScreenChatJQueryTriggers.triggers.resizeChatWindow.call(this, e);
			$scope.il.OnScreenChatJQueryTriggers.triggers.updatePlaceholder.call(this, e);
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
			$menu.close();
		},

		updatePlaceholder: function(e) {
			var $this = $(this),
				placeholder = $this.parent().find('[data-onscreenchat-message-placeholder]');

			if ($.trim($this.html()).length > 0 ) {
				placeholder.addClass('ilNoDisplay');
			} else {
				placeholder.removeClass('ilNoDisplay');
			}
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
							participantsNames = getParticipantsNames(conversation, false);

							header = il.Language.txt('chat_osc_head_grp_x_persons', participantsNames.length);
							var partTooltipFormatter = new ParticipantsTooltipFormatter(participantsNames);
							tooltip = partTooltipFormatter.format();
						} else {
							participantsNames = getParticipantsNames(conversation);
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
			var container = $('[data-onscreenchat-window=' + conversation.id + ']');
			var messages = conversation.messages;
			var messagesHeight = container.find('[data-onscreenchat-body]').outerHeight();

			for (var index in messages) {
				if (
					messages.hasOwnProperty(index) &&
					(!getModule().historyTimestamps.hasOwnProperty(conversation.id) ||
					getModule().historyTimestamps[conversation.id] > messages[index].timestamp)
				) {
					getModule().addMessage(messages[index], true);
				}
			}

			if (
				undefined === getModule().historyTimestamps[conversation.id] ||
				conversation.oldestMessageTimestamp < getModule().historyTimestamps[conversation.id]
			) {
				var newMessagesHeight = container.find('[data-onscreenchat-body]').outerHeight();
				container.find('.panel-body').scrollTop(newMessagesHeight - messagesHeight);
				getModule().historyTimestamps[conversation.id] = conversation.oldestMessageTimestamp;
			}

			getModule().historyBlocked = false;

			container.find('.ilOnScreenChatMenuLoader').closest('div').remove();
		},

		onScroll: function() {
			var container = $(this).closest('[data-onscreenchat-window]');
			var conversation = getModule().storage.get(container.attr('data-onscreenchat-window'));

			if($(this).scrollTop() === 0 && !getModule().historyBlocked && conversation.latestMessage != null) {
				getModule().historyBlocked = true;
				$(this).prepend(
					$('<div></div>').css('text-align', 'center').css('margin-top', '-10px').append(
						$('<img />').addClass("ilOnScreenChatMenuLoader").attr('src', getConfig().loaderImg)
					)
				);
				var oldestMessageTimestamp = getModule().historyTimestamps[conversation.id];
				$chat.getHistory(conversation.id, oldestMessageTimestamp);
			}
		},

		onLogin: function(participant) {
			getModule().user = participant;
		},

		openInviteUser: function(e) {
			e.preventDefault();
			e.stopPropagation();

			$scope.il.Modal.dialogue({
				id: 'modal-' + $(this).attr('data-onscreenchat-add'),
				header: il.Language.txt('chat_osc_invite_to_conversation'),
				show: true,
				body: getModule().config.modalTemplate
						.replace(/\[\[conversationId\]\]/g, $(this).attr('data-onscreenchat-add'))
						.replace('#:#chat_osc_search_modal_info#:#', il.Language.txt('chat_osc_search_modal_info'))
						.replace('#:#chat_osc_user#:#', il.Language.txt('chat_osc_user'))
						.replace('#:#chat_osc_no_usr_found#:#', il.Language.txt('chat_osc_no_usr_found')),
				onShown: function (e, modal) {
					var modalBody = modal.find('[data-onscreenchat-modal-body]'),
						conversation = getModule().storage.get(modalBody.data('onscreenchat-modal-body')),
						$elm = modal.find('input[type="text"]').first();

					$elm.focus().iloscautocomplete({
						appendTo: $elm.parent(),
						requestUrl: getModule().config.userListURL,
						source: function(request, response) {
							var that = this;
							$.getJSON(that.options.requestUrl, {
								term: request.term
							}, function(data) {
								if (typeof data.items === "undefined") {
									if (data.length === 0) {
										modalBody.find('[data-onscreenchat-no-usr-found]').removeClass("ilNoDisplay");
									}
									response(data);
								} else {
									that.more = data.hasMoreResults;
									if (data.items.length === 0) {
										modalBody.find('[data-onscreenchat-no-usr-found]').removeClass("ilNoDisplay");
									}
									response(data.items);
								}
							});
						},
						search: function() {
							var term = this.value;

							if (term.length < 3) {
								return false;
							}

							modalBody.find('label').append(
								$('<img />').addClass("ilOnScreenChatSearchLoader").attr("src", getConfig().loaderImg)
							);
							modalBody.find('[data-onscreenchat-no-usr-found]').addClass("ilNoDisplay");
						},
						response: function() {
							$(".ilOnScreenChatSearchLoader").remove();
						},
						select: function(event, ui) {
							var userId = ui.item.id,
								name   = ui.item.value;

							if (userId > 0) {
								getModule().addUser(conversation.id, userId, name);
								$scope.il.Modal.dialogue({id: "modal-" + conversation.id}).hide();
							}
						}
					});
				}
			});
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

		addMessage: function(messageObject, prepend) {
			var template = getModule().config.messageTemplate;
			var position = (messageObject.userId == getModule().config.userId)? 'right' : 'left';
			var message = messageObject.message.replace(/(?:\r\n|\r|\n)/g, '<br />');
			var chatWindow = $('[data-onscreenchat-window=' + messageObject.conversationId + ']');

			template = template.replace(/\[\[username\]\]/g, findUsernameInConversationByMessage(messageObject));
			template = template.replace(/\[\[time\]\]/g, momentFromNowToTime(messageObject.timestamp));
			template = template.replace(/\[\[time_raw\]\]/g, messageObject.timestamp);
			template = template.replace(/\[\[message]\]/g, getModule().getMessageFormatter().format(message));
			template = template.replace(/\[\[avatar\]\]/g, getProfileImage(messageObject.userId));
			template = template.replace(/\[\[userId\]\]/g, messageObject.userId);

			if (messageObject.hasOwnProperty("isNeutral") && messageObject.isNeutral) {
				template = $(template).find('li.neutral').html();
			} else {
				template = $(template).find('li.' + position).html();
			}

			var chatBody = chatWindow.find('[data-onscreenchat-body]');
			var item = $('<li></li>')
				.addClass(position)
				.addClass('clearfix')
				.append(template);

			if(prepend === true) {
				chatBody.prepend(item);
			} else {
				chatBody.append(item);
			}

			il.ExtLink.autolink(chatBody.find('[data-onscreenchat-body-msg]'));

			if(prepend === false) {
				getModule().scrollBottom(chatWindow);
				getModule().historyBlocked = false;
			}
		},

		resizeWindow: function() {
			var width = $(this).outerWidth();
			var space = parseInt(width / getModule().chatWindowWidth);

			if (space != getModule().numWindows) {
				var openWindows = countOpenChatWindows();
				var diff = openWindows - space;
				getModule().numWindows = space;

				if(diff > 0) {
					for(var i=0; i<diff;i++) {
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

		getMessageFormatter: function() {
			return getModule().messageFormatter;
		},

		getEmoticons: function() {
			return getModule().emoticons;
		}
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

	var ConversationStorage = function ConversationStorage() {

		this.get = function get(id) {
			return JSON.parse(window.localStorage.getItem(PREFIX_CONSTANT + id));
		};

		this.syncUIStateWithStored = function mergeWithStored(conversation) {
			var oldValue = this.get(conversation.id);

			if (oldValue != null && oldValue.open !== undefined && (conversation.open === undefined || conversation.open !== oldValue.open)) {
				conversation.open = oldValue.open;
			}

			return conversation;
		}; 

		this.save = function save(conversation, callback) {
			var oldValue = this.get(conversation.id);

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

			var e = $.Event('storage');
			e.originalEvent = {
				key: PREFIX_CONSTANT + conversation.id,
				oldValue: oldValue,
				newValue: conversation
			};
			$(window).trigger(e);
		};
	};

	var DeferredActivityTrackerFactory = (function () {
		var instances = {
			
		}, ms = 1000;
		
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

	var findUsernameByIdByConversation = function(conversation, usrId) {
		for(var index in conversation.participants) {
			if(conversation.participants.hasOwnProperty(index) && conversation.participants[index].id == usrId) {
				if (getModule().participantsNames.hasOwnProperty(conversation.participants[index].id)) {
					return getModule().participantsNames[conversation.participants[index].id];
				}

				return conversation.participants[index].name;
			}
		}

		return "";
	};

	var findUsernameInConversationByMessage = function(messageObject) {
		var conversation = getModule().storage.get(messageObject.conversationId);

		return findUsernameByIdByConversation(conversation, messageObject.userId);
	};

	var getParticipantsIds = function(conversation) {
		var ids = [];
		for(var index in conversation.participants) {
			if(conversation.participants.hasOwnProperty(index)) {
				ids.push(conversation.participants[index].id);
			}
		}
		return ids;
	};

	var getParticipantsNames = function(conversation, ignoreMySelf) {
		var names = [];

		for (var key in conversation.participants) {
			if (
				conversation.participants.hasOwnProperty(key) && (
					(getModule().user !== undefined && getModule().user.id != conversation.participants[key].id) ||
					ignoreMySelf === false
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

	var ParticipantsTooltipFormatter = function ParticipantsTooltipFormatter(participants) {
		var _participants = participants;

		this.format = function () {
			return $("<ul/>").append(_participants.map(function(elm) {
				return $("<li/>").html("&raquo; "  + elm);
			})).wrap("<div/>").parent().html();
		};
	};

	var getProfileImage = function(userId) {
		if(getModule().participantsImages.hasOwnProperty(userId)) {
			return getModule().participantsImages[userId].src;
		}
		return "";
	};

	var MessagePaster = function(message) {
		var _message = message, getLastCaretPosition = function() {
			return _message.attr("data-onscreenchat-last-caret-pos") || 0;
		};

		this.paste = function(text) {
			var lastCaretPosition = parseInt(getLastCaretPosition(), 10),
				pre  = _message.text().substr(0, lastCaretPosition),
				post = _message.text().substr(lastCaretPosition);

			_message.text(pre + text  + post);

			if (window.getSelection) {
				var node = _message.get(0);
				node.focus();

				var textNode = node.firstChild;
				var range = document.createRange();
				range.setStart(textNode, lastCaretPosition + text.length);
				range.setEnd(textNode, lastCaretPosition + text.length);

				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
			} else {
				_message.focus();
			}
		};
	};

	var MessageFormatter = function MessageFormatter(emoticons) {
		var _emoticons = emoticons;

		this.format = function (message) {
			return _emoticons.replace(message);
		};
	};

	/**
	 * This class renders the smiley selection for ChatActions.
	 * It also replaces all smileys in a chat messages.
	 *
	 * @params {array} _smileys
	 * @constructor
	 */
	var Smileys = function Smileys(_smileys) {
		let emoticonMap = {}, emoticonCollection = [];

		if (typeof _smileys === "object" && Object.keys(_smileys).length > 0) {
			for (let i in _smileys) {
				let prop = _smileys[i];

				if (!emoticonMap.hasOwnProperty(prop)) {
					emoticonMap[prop] = $('<img alt="" title="" />')
						.attr('data-emoticon', i)
						.attr('data-src', prop);
				}

				emoticonMap[prop].attr({
					alt:   [emoticonMap[prop].attr('alt').toString(), i].join(' '),
					title: [emoticonMap[prop].attr('title').toString(), i].join(' ')
				});
			}
			for (let i in emoticonMap) {
				emoticonCollection.push(emoticonMap[i].wrap('<div><a data-onscreenchat-emoticon></a></div>').parent().parent().html());
			}
		}

        /**
         *
         * @param {string} src
         * @returns {Promise<unknown>}
         */
        let Img = function(src) {
            return new Promise(function(resolve, reject) {
                let img = new Image();
                img.addEventListener('load', function(e) {
                    resolve(src)
                    img.addEventListener('error', function() {
                        reject(new Error("Failed to load image's URL: " + src));
                    });
                });
                img.src = src;
            });
        };

		/**
		 * Sets smileys into text
		 *
		 * @param {string} message
		 * @returns {string}
		 */
		this.replace = function (message) {
			if (typeof _smileys === "string") {
				return message;
			}

			for (let i in _smileys) {
				while (message.indexOf(i) !== -1) {
					message = message.replace(i, '<img src="' + _smileys[i] + '" />');
				}
			}

			return message;
		};

		/**
		 * 
		 * @returns {Promise<unknown[]>}
		 */
		this.preload = function () {
			let promises = Object.keys(emoticonMap).map(function (key) {
				return Img(emoticonMap[key].attr('data-src'));
			});

			return Promise.all(promises);
		}

		this.getContent = function () {
			let renderCollection = [];

			emoticonCollection.forEach(function(elm) {
				renderCollection.push(elm.replace(/data-src/, "src"));
			});

			return renderCollection.join('');
		}

		this.getTriggerHtml = function() {
			if (typeof _smileys !== "object" || Object.keys(_smileys).length === 0) {
				return $("");
			}

			return $('<div class="iosOnScreenChatEmoticonsPanel" data-onscreenchat-emoticons-panel><a data-onscreenchat-emoticons-flyout-trigger></a></div>')
				.data('emoticons', this);
		};
	};

})(jQuery, window, window.il.Chat, window.il.OnScreenChatMenu);