
/*$.each(getModule().storage.all(), function(key, conversation){
	if(conversation.open) {
		getModule().open(conversation.id);
	}
});*/


(function($, $scope, $chat, $menu){
	'use strict';

	var smileys = {};

	$scope.il.OnScreenChatJQueryTriggers = {
		triggers: {
			participantEvent: function(){},
			closeEvent: function(){},
			submitEvent: function(){},
			addEvent: function(){},
			searchEvent: function(){},
			resizeChatWindow: function() {},
			focusOut: function() {}
		},

		setTriggers: function(triggers) {
			if(triggers.hasOwnProperty('participantEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent = triggers.participantEvent;
			}
			if(triggers.hasOwnProperty('closeEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.closeEvent = triggers.closeEvent;
			}
			if(triggers.hasOwnProperty('submitEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent = triggers.submitEvent;
			}
			if(triggers.hasOwnProperty('addEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.addEvent = triggers.addEvent;
			}
			if(triggers.hasOwnProperty('searchEvent')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.searchEvent = triggers.searchEvent;
			}
			if(triggers.hasOwnProperty('resizeChatWindow')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.resizeChatWindow = triggers.resizeChatWindow;
			}
			if(triggers.hasOwnProperty('focusOut')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.focusOut = triggers.focusOut;
			}

			return this;
		},

		init: function() {
			$('body')
				.on('click', '[data-onscreenchat-userid]', $scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent)
				.on('click', '[data-onscreenchat-close]', $scope.il.OnScreenChatJQueryTriggers.triggers.closeEvent)
				.on('click', '[data-onscreenchat-submit]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)
				.on('click', '[data-onscreenchat-add]', $scope.il.OnScreenChatJQueryTriggers.triggers.addEvent)
				.on('click', '[data-onscreenchat-menu-item]', $scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent)
				.on('click', '[data-onscreenchat-window]', function(e){
					e.preventDefault();
					e.stopPropagation();
					$(this).find('[data-onscreenchat-message]').focus();
				})
				.on('keydown', '[data-onscreenchat-usersearch]', $scope.il.OnScreenChatJQueryTriggers.triggers.searchEvent)
				.on('keydown', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)
				.on('input', '[data-onscreenchat-message]', $scope.il.OnScreenChatJQueryTriggers.triggers.resizeChatWindow)
				.on('focusout', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.focusOut)
				.on('click', '[data-onscreenchat-emoticons-flyout] a', function(e) {
					var conversationWindow = $(this).closest('[data-onscreenchat-window]'),
						flyoutPanelTrigger = conversationWindow.find('[data-onscreenchat-emoticons-flyout-trigger]');

					e.preventDefault();
					e.stopPropagation();

					flyoutPanelTrigger.click();
					conversationWindow.find('div[data-onscreenchat-message]').append($(this).find('img').data("emoticon"));
				})
			
				/*.on('keydown', '[data-onscreenchat-message]', function(e) {
					console.log("shift + enter event");
				}).on('input', '[data-onscreenchat-message]', function() {
					console.log("resizeEvent");
				})*/;
		}
	};



	$scope.il.OnScreenChat = {
		config: {},
		container: $('<div></div>').addClass('row'),
		storage: undefined,
		user: undefined,
		historyBlocked: false,
		inputHeight: undefined,
		historyTimestamps: {},
		emoticons: {},

		setConfig: function(config) {
			getModule().config = config;
		},

		init: function() {
			getModule().storage   = new ConversationStorage();
			getModule().emoticons = new Smileys(getModule().config.emoticons);
			
			$menu.setEmoticons(getModule().getEmoticons());

			$(window).bind('storage', function(e){
				var conversation = e.originalEvent.newValue;
				var chatWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

				if(!(conversation instanceof Object)) {
					conversation = JSON.parse(conversation);
				}

				//$menu.add(conversation);

				if(conversation.open && !chatWindow.is(':visible')) {
					getModule().open(conversation);
				} else if(!conversation.open) {
					chatWindow.hide();
				}
			});

			$chat.init(getConfig().userId, getConfig().username, getModule().onLogin);
			$chat.receiveMessage(getModule().receiveMessage);
			$chat.receiveConversation(getModule().onConversation);
			$chat.onHistory(getModule().onHistory);
			$chat.onGroupConversation(getModule().onConversationInit);
			$chat.onConverstionInit(getModule().onConversationInit);
			$scope.il.OnScreenChatJQueryTriggers.setTriggers({
				participantEvent: getModule().startConversation,
				closeEvent: getModule().close,
				submitEvent: getModule().handleSubmit,
				addEvent: getModule().openInviteUser,
				searchEvent: getModule().searchUser,
				resizeChatWindow: getModule().resizeMessageInput,
				focusOut: getModule().onFocusOut
			}).init();

			$('body').append(
				$('<div></div>')
					.attr('id', 'onscreenchat-container')
					.addClass('container')
					.append(getModule().container)
			);
		},

		startConversation: function(e){
			e.preventDefault();
			e.stopPropagation();

			var link = $(this);
			var conversationId = $(link).attr('data-onscreenchat-conversation');
			var participant = { id: $(link).attr('data-onscreenchat-userid'), name: $(link).attr('data-onscreenchat-username') };
			var conversation = getModule().storage.get(conversationId);

			if (typeof il.Awareness != "undefined") {
				il.Awareness.close();
			}

			if(conversation == null) {
				$chat.getConversation([getModule().user, participant]);
				return;
			}

			conversation.open = true;
			getModule().storage.save(conversation);
		},

		open: function(conversation) {
			var conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

			if(conversationWindow.length == 0) {
				conversationWindow = $(getModule().createWindow(conversation));
				conversationWindow.find('.panel-body').scroll(getModule().onScroll);
				conversationWindow
					.find('[data-onscreenchat-emoticons]')
					.append(getModule().getEmoticons().getHtml())
					.find('.iosChatEmoticonsPanel')
					.parent().removeClass("ilNoDisplay");
				getModule().container.append(conversationWindow);
				getModule().addMessagesOnOpen(conversation);
			}

			getModule().scrollBottom(conversationWindow);


			if(conversation.latestMessage != null) {
				$chat.getHistory(conversation.id, getModule().historyTimestamps[conversation.id]);
			}

			conversationWindow.show();
			getModule().resizeMessageInput.call($(conversationWindow).find('[data-onscreenchat-message]'));
		},

		scrollBottom: function(chatWindow) {
			$(chatWindow).find('.panel-body').animate({
				scrollTop: $(chatWindow).find('[data-onscreenchat-body]').outerHeight()
			}, 0);
		},

		resizeMessageInput: function(){
			var inputWrapper = $(this).closest('.panel-footer');
			var parent = $(inputWrapper).closest('[data-onscreenchat-window]');
			var wrapperHeight = parent.outerHeight();
			var headingHeight = parent.find('.panel-heading').outerHeight();
			var inputHeight = $(inputWrapper).outerHeight();
			var bodyHeight = wrapperHeight - inputHeight - headingHeight;

			parent.find('.panel-body').css('height', bodyHeight + "px");
		},

		createWindow: function(conversation) {
			var template = getModule().config.chatWindowTemplate;
			var participantsName = [];

			for(var key in conversation.participants) {
				if(getModule().user.id != conversation.participants[key].id) {
					participantsName.push(conversation.participants[key].name);
				}
			}

			template = template.replace('[[participants]]', participantsName.join(', '));
			template = template.replace(/\[\[conversationId\]\]/g, conversation.id);

			return template;
		},

		close: function() {
			var button = $(this);
			var conversation = getModule().storage.get($(button).attr('data-onscreenchat-close'));
			conversation.open = false;

			getModule().storage.save(conversation);
		},

		handleSubmit: function(e) {
			if((e.keyCode == 13 && !e.shiftKey) || e.type == 'click')
			{
				e.preventDefault();
				var conversationId = $(this).closest('[data-onscreenchat-window]').attr('data-onscreenchat-window');
				getModule().send(conversationId);
			}
		},

		send: function(conversationId) {
			var input = $('[data-onscreenchat-window=' + conversationId + ']').find('[data-onscreenchat-message]');
			var message = input.html();

			if(message != "") {
				$chat.sendMessage(conversationId, message);
				input.html('');
				getModule().resizeMessageInput.call(input);
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
			conversation.open = true;
			getModule().storage.save(conversation);
			getModule().addMessage(messageObject, false);
			conversation.latestMessage = messageObject;
			getModule().storage.save(conversation);
			$menu.add(conversation);
		},

		onConversationInit: function(conversation){
			conversation.open = true;
			$menu.add(conversation);
			getModule().storage.save(conversation);
		},

		onFocusOut: function() {
			var conversation = getModule().storage.get($(this).attr('data-onscreenchat-window'));
			conversation.lastActivity = (new Date()).getTime();

			$chat.trackActivity(conversation.id, getModule().user.id, conversation.lastActivity);
		},

		onConversation: function(conversation) {
			console.log(conversation.id);
			$menu.add(conversation);
			getModule().storage.save(conversation);
		},

		onHistory: function(conversation){
			var container = $('[data-onscreenchat-window='+conversation.id+']');
			var messages = conversation.messages;
			var messagesHeight = container.find('[data-onscreenchat-body]').outerHeight();

			for(var index in messages) {
				if(messages.hasOwnProperty(index)){
					getModule().addMessage(messages[index], true);
				}
			}
			var newMessagesHeight = container.find('[data-onscreenchat-body]').outerHeight();
			container.find('.panel-body').scrollTop(newMessagesHeight - messagesHeight);

			getModule().historyTimestamps[conversation.id] = conversation.oldestMessageTimestamp;
			getModule().historyBlocked = false;

			container.find('.ilOnScreenChatMenuLoader').closest('div').remove();
		},

		onScroll: function() {
			var container = $(this).closest('[data-onscreenchat-window]');
			var conversation = getModule().storage.get(container.attr('data-onscreenchat-window'));

			if($(this).scrollTop() == 0 && !getModule().historyBlocked && conversation.latestMessage != null) {
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

		openInviteUser: function() {
			$scope.il.Modal.dialogue({
				header: "Invite user to conversation",
				show: true,
				body: getModule().config.modalTemplate.replace(/\[\[conversationId\]\]/g, $(this).attr('data-onscreenchat-add'))
			});
		},

		addMessage: function(messageObject, prepend) {
			var template = getModule().config.messageTemplate;
			var position = (messageObject.userId == getModule().config.userId)? 'right' : 'left';
			var  message = messageObject.message.replace(/(?:\r\n|\r|\n)/g, '<br />');
			var chatWindow = $('[data-onscreenchat-window=' + messageObject.conversationId + ']');

			template = template.replace(/\[\[username\]\]/g, findUsernameInConversation(messageObject));
			template = template.replace(/\[\[time\]\]/g, momentFromNowToTime(messageObject.timestamp));
			template = template.replace(/\[\[message]\]/g, getModule().getEmoticons().replace(message));
			template = template.replace(/\[\[avatar\]\]/g, (messageObject.userId == getModule().config.userId)? 'http://placehold.it/50/FA6F57/fff&amp;text=ME' : 'http://placehold.it/50/55C1E7/fff&amp;text=U');
			template = $(template).find('li.' + position).html();

			var chatBody = chatWindow.find('[data-onscreenchat-body]');
			var item = $('<li></li>')
				.addClass(position)
				.addClass('clearfix')
				.append(template);

			if(prepend == true) {
				chatBody.prepend(item);
			} else {
				chatBody.append(item);
			}

			if(position == 'right') {
				getModule().scrollBottom(chatWindow);
			}
		},

		searchUser: function() {
			if($(this).val().length > 2) {
				$.get(
					getModule().config.userListURL + '&q=' + $('#invite_user_text').val(),
					function(response){
						var list = $('[data-onscreenchat-userlist]');
						list.children().remove();

						$(response.items).each(function() {
							console.log(this);
							var userId = this.id;
							var name = this.value;
							var link = $('<a></a>')
								.prop('href', '#')
								.text(name)
								.click(function (e) {
									e.preventDefault();
									e.stopPropagation();
									getModule().addUser($(this).closest("ul").attr('data-onscreenchat-userlist'), userId, name)
								});
							var line =  $('<li></li>')
								.addClass('invite_user_line_id')
								.addClass('invite_user_line')
								.append(link);

							list.append(line);
						});
					},
					'json'
				);
			} else {
				$('#invite_users_available').children().remove();
			}
		},

		addUser: function(conversationId, userId, name) {
			$chat.addUser(conversationId, userId, name);
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

	var ConversationStorage = function ConversationStorage() {
		this.get = function(id) {
			return JSON.parse(window.localStorage.getItem(id));
		};

		this.save = function(conversation) {
			var oldValue = this.get(conversation.id);
			conversation.messages = [];

			if(conversation.open == undefined && oldValue != null) {
				conversation.open = oldValue.open;
			}

			window.localStorage.setItem(conversation.id, JSON.stringify(conversation));

			var e = $.Event('storage');
			e.originalEvent = {
				key: conversation.id,
				oldValue: oldValue,
				newValue: conversation
			};
			$(window).trigger(e);
		};
	};

	var findUsernameInConversation = function(messageObject) {
		var conversation = getModule().storage.get(messageObject.conversationId);

		for(var index in conversation.participants) {
			if(conversation.participants.hasOwnProperty(index) && conversation.participants[index].id == messageObject.userId) {
				return conversation.participants[index].name;
			}
		}
		return "";
	};

	/**
	 * This class renders the smiley selection for ChatActions.
	 * It also replaces all smileys in a chat messages.
	 *
	 * @params {array} _smileys
	 * @constructor
	 */
	var Smileys = function Smileys(_smileys) {
		/**
		 * Sets smileys into text
		 *
		 * @param {string} message
		 * @returns {string}
		 */
		this.replace = function (message) {
			if (typeof _smileys == "string") {
				return message;
			}

			for (var i in _smileys) {
				while (message.indexOf(i) != -1) {
					message = message.replace(i, '<img src="' + _smileys[i] + '" />');
				}
			}

			return message;
		};

		this.getHtml = function() {
			if (typeof _smileys == "object") {
				if (_smileys.length == 0) {
					return $("");
				}

				var $emoticons_flyout_trigger = $('<a data-onscreenchat-emoticons-flyout-trigger></a>');
				var $emoticons_flyout = $('<div class="iosChatEmoticonsPanelFlyout" data-onscreenchat-emoticons-flyout></div>');
				var $emoticons_panel = $('<div class="iosChatEmoticonsPanel" data-onscreenchat-emoticons-panel></div>')
					.append($emoticons_flyout_trigger)
					.append($emoticons_flyout);

				var $emoticons_table = $("<table></table>");
				var $emoticons_row = null;
				var cnt = 0;
				var emoticonMap = {};
				for (var i in _smileys) {
					var $emoticon;
					if (emoticonMap[_smileys[i]]) {
						$emoticon = emoticonMap[_smileys[i]];
					} else {
						if (cnt % 6 == 0) {
							$emoticons_row = $("<tr></tr>");
							$emoticons_table.append($emoticons_row);
						}

						$emoticon = $('<img src="' + _smileys[i] + '" alt="" title="" />');
						$emoticon.data("emoticon", i);
						$emoticons_row.append($('<td></td>').append($('<a></a>').append($emoticon)));

						emoticonMap[_smileys[i]] = $emoticon;

						++cnt;
					}
					$emoticon.attr({
						alt:   [$emoticon.attr('alt').toString(), i].join(' '),
						title: [$emoticon.attr('title').toString(), i].join(' ')
					});
				}
				$emoticons_flyout.append($emoticons_table);

				$emoticons_flyout_trigger.click(function (e) {
					$(this)
						.closest('[data-onscreenchat-window]')
						.find('[data-onscreenchat-emoticons-flyout]')
						.toggle();
				}).toggle(function(e) {
					$(this).addClass("active");
				}, function(e) {
					$(this).removeClass("active");
				});

				$emoticons_panel.on('clickoutside', function(e) {
					var conversationWindow = $(this).closest('[data-onscreenchat-window]'),
						flyoutTconversationWindow = conversationWindow.find('[data-onscreenchat-emoticons-flyout-trigger]');

					if (flyoutTconversationWindow.hasClass("active")) {
						flyoutTconversationWindow.click();
					}
				});

				return $emoticons_panel;
			}
		};
	};

})(jQuery, window, window.il.Chat, window.il.OnScreenChatMenu);