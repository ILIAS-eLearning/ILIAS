
/*$.each(getModule().storage.all(), function(key, conversation){
	if(conversation.open) {
		getModule().open(conversation.id);
	}
});*/


(function($, $scope, $chat, $menu){
	'use strict';

	var delayedUserSearch = (function(){
		var timer = 0;

		return function(callback, ms){
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	$scope.il.OnScreenChatJQueryTriggers = {
		triggers: {
			participantEvent: function(){},
			closeEvent: function(){},
			submitEvent: function(){},
			addEvent: function(){},
			searchEvent: function(){},
			resizeChatWindow: function() {},
			focusOut: function() {},
			messageInput: function() {}
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
			if(triggers.hasOwnProperty('messageInput')) {
				$scope.il.OnScreenChatJQueryTriggers.triggers.messageInput = triggers.messageInput;
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
				.on('keyup', '[data-onscreenchat-usersearch]', $scope.il.OnScreenChatJQueryTriggers.triggers.searchEvent)
				.on('keydown', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)
				.on('input', '[data-onscreenchat-message]', $scope.il.OnScreenChatJQueryTriggers.triggers.resizeChatWindow)
				.on('keyup click', '[data-onscreenchat-message]', $scope.il.OnScreenChatJQueryTriggers.triggers.messageInput)
				.on('focusout', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.focusOut)
				.on('click', '[data-onscreenchat-emoticon]', function(e) {
					var conversationWindow = $(this).closest('[data-onscreenchat-window]');

					e.preventDefault();
					e.stopPropagation();

					/*console.log("Clicked " + $(this).find('img').data('emoticon'));
					var messageField = conversationWindow.find('[data-onscreenchat-message]');
					console.log("Last Pos " + messageField.data("onscreenchat-last-caret-pos"));*/

					conversationWindow.find('[data-onscreenchat-message]')
						.popover('hide')
						.append($(this).find('img').data('emoticon'))

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
		participantsImages: {},

		setConfig: function(config) {
			getModule().config = config;

			moment.locale(config.locale);
			window.setInterval(function() {
				$('[data-livestamp]').each(function() {
					var $this = $(this);
					$this.html(momentFromNowToTime($this.data('livestamp')));
				});
			}, 10000);
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
				focusOut: getModule().onFocusOut,
				messageInput: getModule().onMessageInput
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

			if (conversationWindow.length == 0) {
				conversationWindow = $(getModule().createWindow(conversation));
				conversationWindow.find('.panel-body').scroll(getModule().onScroll);
				conversationWindow
					.find('[data-onscreenchat-emoticons]')
					.append(getModule().getEmoticons().getHtml())
					.find('.iosOnScreenChatEmoticonsPanel')
					.parent()
					.removeClass('ilNoDisplay');
				getModule().container.append(conversationWindow);
				getModule().addMessagesOnOpen(conversation);

				var emoticonPanel = conversationWindow.find('[data-onscreenchat-emoticons-panel]'),
					messageField = conversationWindow.find('[data-onscreenchat-message]');

				messageField.popover({
					html : true,
					trigger: 'manual',
					placement : 'auto',
					title: il.Language.txt('chat_osc_emoticons'),
					content: function () {
						return emoticonPanel.data('emoticons').join(' ');
					}
				});

				emoticonPanel.find('[data-onscreenchat-emoticons-flyout-trigger]').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					messageField.popover('show');
				}).on('clickoutside', function(e) {
					e.preventDefault();
					e.stopPropagation();

					messageField.popover('hide');
				});
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

		resizeMessageInput: function(e){
			var inputWrapper = $(this).closest('.panel-footer');
			var parent = $(inputWrapper).closest('[data-onscreenchat-window]');
			var wrapperHeight = parent.outerHeight();
			var headingHeight = parent.find('.panel-heading').outerHeight();
			var inputHeight = $(inputWrapper).outerHeight();
			var bodyHeight = wrapperHeight - inputHeight - headingHeight;

			if($(this).html() == "<br>") {
				$(this).html("");
			}

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
			template = template.replace('#:#close#:#', il.Language.txt('close'));
			template = template.replace('#:#chat_osc_write_a_msg#:#', il.Language.txt('chat_osc_write_a_msg'));
			template = template.replace('#:#chat_osc_add_user#:#', il.Language.txt('chat_osc_add_user'));
			template = template.replace('#:#chat_osc_send#:#', il.Language.txt('chat_osc_send'));

			return template;
		},

		close: function() {
			var button = $(this);
			var conversation = getModule().storage.get($(button).attr('data-onscreenchat-close'));
			conversation.open = false;

			getModule().storage.save(conversation);
		},

		handleSubmit: function(e) {
			if ((e.keyCode == 13 && !e.shiftKey) || e.type == 'click') {
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

			if(getModule().historyTimestamps[conversation.id] == undefined) {
				getModule().historyTimestamps[conversation.id] = messageObject.timestamp;
			}

			getModule().storage.save(conversation);
			getModule().addMessage(messageObject, false);
			conversation.latestMessage = messageObject;
			getModule().storage.save(conversation);
			$menu.add(conversation);
		},

		requestUserImages: function(conversation) {
			var participantsIds = getParticipantsIds(conversation);
			participantsIds = participantsIds.filter(function(id){
				return !getModule().participantsImages.hasOwnProperty(id);
			});

			$.get(
				getModule().config.userProfileDataURL + '&usr_ids=' + participantsIds.join(','),
				function (response){
					$.each(response, function(id, item){
						var img = new Image();
						img.src = item.profile_image;
						getModule().participantsImages[id] = img;

						$('[data-onscreenchat-avatar='+id+']').attr('src', img.src);
						$menu.syncProfileImages(getModule().participantsImages);
					});
				},
				'json'
			);
		},

		onConversationInit: function(conversation){
			getModule().requestUserImages(conversation);
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
			getModule().requestUserImages(conversation);
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

		openInviteUser: function(e) {
			e.preventDefault();
			e.stopPropagation();

			$scope.il.Modal.dialogue({
				id: 'modal-' + $(this).attr('data-onscreenchat-add'),
				header: il.Language.txt('chat_osc_invite_to_conversation'),
				show: true,
				body: getModule().config.modalTemplate
						.replace(/\[\[conversationId\]\]/g, $(this).attr('data-onscreenchat-add'))
						.replace('#:#username#:#', il.Language.txt('username')),
				onShow: function (e, modal) {
					modal.find('input[type="text"]').first().focus();
				}
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

		onMessageInput: function(e) {
			var $this = $(this);

			$this.attr("data-onscreenchat-last-caret-pos", getModule().getCaretPosition($this.get(0)));
		},

		addMessage: function(messageObject, prepend) {
			console.log("addMessages");
			var template = getModule().config.messageTemplate;
			var position = (messageObject.userId == getModule().config.userId)? 'right' : 'left';
			var  message = messageObject.message.replace(/(?:\r\n|\r|\n)/g, '<br />');
			var chatWindow = $('[data-onscreenchat-window=' + messageObject.conversationId + ']');

			template = template.replace(/\[\[username\]\]/g, findUsernameInConversation(messageObject));
			template = template.replace(/\[\[time\]\]/g, momentFromNowToTime(messageObject.timestamp));
			template = template.replace(/\[\[time_raw\]\]/g, messageObject.timestamp);
			template = template.replace(/\[\[message]\]/g, getModule().getEmoticons().replace(message));
			template = template.replace(/\[\[avatar\]\]/g, getProfileImage(messageObject.userId));
			template = template.replace(/\[\[userId\]\]/g, messageObject.userId);
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

		searchUser: function(e) {
			var $input = $(this),
				modalBody = $input.closest('[data-onscreenchat-modal-body]');

			delayedUserSearch(function (e) {
				if ($input.val().length > 2) {
					$.get(
						getModule().config.userListURL + '&q=' + $input.val(),
						function (response){
							var conversation = getModule().storage.get(modalBody.data('onscreenchat-modal-body'));
							var list = modalBody.find('[data-onscreenchat-userlist]');

							list.addClass("ilNoDisplay").children().remove();

							$(response.items).each(function() {
								if (!userExistsInConversation(this.id, conversation)) {
									var userId = this.id,
										name = this.value,
										link = $('<a></a>')
											.prop('href', '#')
											.text(name)
											.click(function (e) {
												e.preventDefault();
												e.stopPropagation();

												getModule().addUser($(this).closest("ul").attr('data-onscreenchat-userlist'), userId, name);
												$scope.il.Modal.dialogue({id: 'modal-' + conversation.id}).hide();
											}),
										line = $('<li></li>').append(link);
										list.removeClass("ilNoDisplay").append(line);
								}
							});
						},
						'json'
					);
				} else {
					modalBody.find('[data-onscreenchat-userlist]').addClass("ilNoDisplay").children().remove();
				}
			}, 500);
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

	var userExistsInConversation = function(userId, conversation) {
		for (var index in conversation.participants) {
			if(conversation.participants.hasOwnProperty(index) && conversation.participants[index].id == userId) {
				return true;
			}
		}
		return false;
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

	var getProfileImage = function(userId) {
		if(getModule().participantsImages.hasOwnProperty(userId)) {
			return getModule().participantsImages[userId].src;
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

		if (_smileys.length != 0) {
			// Fetch them directly to prevent issues with the ILIAS WAC
			for (var i in _smileys) {
				var img = new Image();
				img.src = _smileys[i];
			}
		}

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

				var emoticonMap = {}, emoticonCollection = [];

				for (var i in _smileys) {
					var prop = _smileys[i];

					if (!emoticonMap.hasOwnProperty(prop)) {
						var $emoticon = $('<img src="' + prop + '" alt="" title="" />')
							.attr('data-emoticon', i);

						emoticonMap[prop] = $emoticon;
					}

					emoticonMap[prop].attr({
						alt:   [emoticonMap[prop].attr('alt').toString(), i].join(' '),
						title: [emoticonMap[prop].attr('title').toString(), i].join(' ')
					});
				}

				for (var i in emoticonMap) {
					emoticonCollection.push(emoticonMap[i].wrap('<div><a data-onscreenchat-emoticon></a></div>').parent().parent().html());
				}

				var emoticonsElm = $('<div class="iosOnScreenChatEmoticonsPanel" data-onscreenchat-emoticons-panel><a data-onscreenchat-emoticons-flyout-trigger></a></div>')
					.data('emoticons', emoticonCollection);

				return emoticonsElm;
			}
		};
	};

})(jQuery, window, window.il.Chat, window.il.OnScreenChatMenu);