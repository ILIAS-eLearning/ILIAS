
/*$.each(getModule().storage.all(), function(key, conversation){
	if(conversation.open) {
		getModule().open(conversation.id);
	}
});*/


(function($, $scope, $chat, $menu){
'use strict';
	$scope.il.OnScreenChatJQueryTriggers = {
		triggers: {
			participantEvent: function(){},
			closeEvent: function(){},
			submitEvent: function(){},
			addEvent: function(){},
			searchEvent: function(){}
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

			return this;
		},

		init: function() {
			$('body')
				.on('click', '[data-onscreenchat-userid]', $scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent)
				.on('click', '[data-onscreenchat-close]', $scope.il.OnScreenChatJQueryTriggers.triggers.closeEvent)
				.on('click', '[data-onscreenchat-submit]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)
				.on('click', '[data-onscreenchat-add]', $scope.il.OnScreenChatJQueryTriggers.triggers.addEvent)
				.on('click', '[data-onscreenchat-menu-item]', $scope.il.OnScreenChatJQueryTriggers.triggers.participantEvent)
				.on('keydown', '[data-onscreenchat-usersearch]', $scope.il.OnScreenChatJQueryTriggers.triggers.searchEvent)
				.on('keydown', '[data-onscreenchat-window]', $scope.il.OnScreenChatJQueryTriggers.triggers.submitEvent)

				/*.on('keydown', '[data-onscreenchat-message]', function(e) {
					console.log("shift + enter event");
				}).on('input', '[data-onscreenchat-message]', function() {
					console.log("resizeEvent");
				})*/;

			$(document).on('scroll', 'div.panel-body', function() {
				console.log($(this).scrollTop)
			})
		}
	};



	$scope.il.OnScreenChat = {
		config: {},
		container: $('<div></div>').addClass('row'),
		storage: undefined,
		user: undefined,

		setConfig: function(config) {
			getModule().config = config;
		},

		init: function() {
			localStorage.clear();
			getModule().storage = new ConversationStorage();

			$(window).bind('storage', function(e){
				var conversation = e.originalEvent.newValue;
				if(!(conversation instanceof Object)) {
					conversation = JSON.parse(conversation);
				}

				$menu.add(conversation);

				if(conversation.open) {
					getModule().open(conversation);
				} else {
					$('[data-onscreenchat-window=' + conversation.id + ']').hide();
				}
			});

			$chat.init(getConfig().userId, getConfig().username, getModule().onLogin);
			$chat.receiveMessage(getModule().receiveMessage);
			$chat.receiveConversation(function(conversation) {
				getModule().storage.save(conversation);
			});
			$scope.il.OnScreenChatJQueryTriggers.setTriggers({
				participantEvent: getModule().startConversation,
				closeEvent: getModule().close,
				submitEvent: getModule().handleSubmit,
				addEvent: getModule().openInviteUser,
				searchEvent: getModule().searchUser
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
				$chat.getConversation([getModule().user, participant], function(conversation) {
					conversation.open = true;
					getModule().storage.save(conversation);
				});
				return;
			}

			conversation.open = true;
			getModule().storage.save(conversation);
		},

		open: function(conversation) {
			var conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

			if(conversationWindow.length == 0)
			{
				conversationWindow = $(getModule().createWindow(conversation));
				getModule().container.append(conversationWindow);
				getModule().addMessagesFromHistory(conversation);
			}
			conversationWindow.show();
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
			if(e.keyCode == 13 && !e.shiftKey)
			{
				var conversationId = $(this).closest('[data-onscreenchat-window]').attr('data-onscreenchat-window');
				getModule().send(conversationId);
			}
		},

		send: function(conversationId) {
			var input = $('[data-onscreenchat-window=' + conversationId + ']').find('[data-onscreenchat-message]');
			var message = input.val();
			if(message != "")
			{
				$chat.sendMessage(conversationId, message);
				input.val('')
			}
		},

		resizeInput: function(input) {
			$(input).height(1);
			var totalHeight = $(input).prop('scrollHeight') - parseInt($(input).css('padding-top')) - parseInt($(input).css('padding-bottom'));
			$(input).height(totalHeight);
		},

		addMessagesFromHistory: function(conversation) {
			if(conversation.messages.length > 0) {
				var messages = conversation.messages.reverse();
				for(var index in messages) {
					if(messages.hasOwnProperty(index)) {
						getModule().receiveMessage(messages[index]);
					}
				}
			}
		},

		receiveMessage: function(messageObject) {
			var template = getModule().config.messageTemplate;
			var position = (messageObject.userId == getModule().config.userId)? 'right' : 'left';
			var  message = messageObject.message.replace(/(?:\r\n|\r|\n)/g, '<br />');

			template = template.replace(/\[\[username\]\]/g, findUsernameInConversation(messageObject));
			template = template.replace(/\[\[time\]\]/g, momentFromNowToTime(messageObject.timestamp));
			template = template.replace(/\[\[message]\]/g, message);
			template = template.replace(/\[\[avatar\]\]/g, (messageObject.userId == getModule().config.userId)? 'http://placehold.it/50/FA6F57/fff&amp;text=ME' : 'http://placehold.it/50/55C1E7/fff&amp;text=U');
			template = $(template).find('li.' + position).html();


			$('[data-onscreenchat-window=' + messageObject.conversationId + ']').find('[data-onscreenchat-body]').append(
				$('<li></li>')
					.addClass(position)
					.addClass('clearfix')
					.append(template)
			);
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
			$chat.addUser(conversationId, userId, name, function(){
				$scope.il.Modal.hide();
			});
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

})(jQuery, window, window.il.Chat, window.il.OnScreenChatMenu);