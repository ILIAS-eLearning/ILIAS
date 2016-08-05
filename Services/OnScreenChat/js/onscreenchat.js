(function($, $scope, $chat){
	$scope.il.OnScreenChat = {
		config: {},
		container: undefined,
		storage: undefined,

		setConfig: function(config) {
			$scope.il.OnScreenChat.config = config;
		},

		init: function() {
			//localStorage.clear();
			getModule().container = $('<div></div>').addClass('row');
			getModule().storage = new Storage();

			$.each(getModule().storage.all(), function(key, conversation){
				if(conversation.open) {
					getModule().open(conversation.id);
				}
			});

			$('body').append(
				$('<div></div>')
					.attr('id', 'onscreenchat-container')
					.addClass('container')
					.append(getModule().container)
			).on('click', '[data-participant]', function(e) {
				e.preventDefault();
				var imActionLink = $(this);
				var dataConversationId = imActionLink.attr('data-conversation');

				if(dataConversationId == undefined) {
					var participants = [imActionLink.attr('data-participant'), getModule().config.userId];

					$chat.getConversation(participants, function(conversationId) {
						imActionLink.attr('data-conversation', conversationId);
						getModule().start(conversationId, participants);
					});
				} else {
					getModule().open(getModule.storage.find(dataConversationId));
				}
			}).on('click', '[data-onscreenchat-close]', function() {
				getModule().close($(this).attr('data-onscreenchat-close'));
			}).on('click', '[data-onscreenchat-submit]', function() {
				getModule().handleSubmit($(this));
			}).on('keydown', '[data-onscreenchat-window]', function(e) {
				if(e && e.keyCode == 13 && !e.shiftKey) {
					getModule().handleSubmit($(this));
				}
			}).on('keydown', '[data-onscreenchat-message]', function(e) {
				if(e.which == 13 && !e.shiftKey) {
					e.preventDefault();
				}
			}).on('input', '[data-onscreenchat-message]', function() {
				getModule().resizeInput($(this));
			});

			$chat.receiveMessage(getModule().receiveMessage);
		},

		start: function(conversationId, participants) {
			var conversation = getModule().storage.find(conversationId);
			if(conversation === null)
			{
				conversation = new Conversation(conversationId, [participants]);
			}

			getModule().open(conversation);
		},

		open: function(conversation) {
			var conversationWindow = $('[data-onscreenchat-window=' + conversation.id + ']');

			if(conversationWindow.length == 0)
			{
				conversationWindow = $(getModule().createWindow(conversation.id, conversation.participants));
				getModule().container.append(conversationWindow);
			}

			conversation.open = true;
			conversationWindow.show();

			getModule().storage.add(conversation);
		},

		createWindow: function(conversationId, participants) {
			var template = getModule().config.chatWindowTemplate;

			template = template.replace('[[participants]]', participants.join(', '));
			template = template.replace(/\[\[conversationId\]\]/g, conversationId);

			return template;
		},

		addMessage: function(conversationId, from, message, timestamp) {
			var template = getModule().config.messageTemplate;
			var position = (from == getModule().config.userId)? 'right' : 'left';

			console.log(template);

			message = message.replace(/(?:\r\n|\r|\n)/g, '<br />');

			template = template.replace(/\[\[username\]\]/g, from);
			template = template.replace(/\[\[time\]\]/g, momentFromNowToTime((new Date()).setTime(timestamp), "DD.MM.YYYY H:mm"));
			template = template.replace(/\[\[message]\]/g, message);
			template = template.replace(/\[\[avatar\]\]/g, (from == getModule().config.userId)? 'http://placehold.it/50/FA6F57/fff&amp;text=ME' : 'http://placehold.it/50/55C1E7/fff&amp;text=U');

			template = $(template).find('li.' + position).html();

			console.log($(['data-onscreenchat-window=' + conversationId + ']']));

			$('[data-onscreenchat-window=' + conversationId + ']').find('[data-onscreenchat-body]').append(
				$('<li></li>')
					.addClass(position)
					.addClass('clearfix')
					.append(template)
			);
		},

		close: function(conversationId) {
			var conversation = getModule().storage.find(conversationId);
			conversation.open = false;
			console.log("close");
			$('[data-onscreenchat-window=' + conversationId + ']').hide();

			getModule().storage.add(conversation);
		},

		handleSubmit: function(trigger) {
			var conversationId = $(trigger).closest('[data-onscreenchat-window]').attr('data-onscreenchat-window');


			getModule().send(conversationId);
			//getModule().resizeInput(window.find('.chat-message'));
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

		receiveMessage: function(messageObject) {
			getModule().addMessage(messageObject.conversationId, messageObject.userId, messageObject.message, messageObject.timestamp);
		}
	};

	/**
	 * @returns {window.il.OnScreenChat}
	 */
	function getModule() {
		return $scope.il.OnScreenChat;
	}

	var Conversation = function Conversation(id, participants) {
		this.id = id;
		this.participants = participants;
		this.open = false;
	};

	var Storage = function Storage() {
		const _STORAGE_KEY = 'onscreenchat';

		if(localStorage.getItem(_STORAGE_KEY) == null)
		{
			localStorage.setItem(_STORAGE_KEY, JSON.stringify({}));
		}

		this.all = function() {
			return _load();
		};

		this.find = function(conversationId) {
			var conversations = _load();

			if(conversations.hasOwnProperty(conversationId)) {
				return conversations[conversationId];
			}

			return null;
		};

		this.add = function(conversation) {
			var conversations = _load();

			conversations[conversation.id] = conversation;

			_save(conversations);
		};

		var _load = function() {
			return JSON.parse(localStorage.getItem(_STORAGE_KEY));
		};

		var _save = function(data) {
			localStorage.setItem(_STORAGE_KEY, JSON.stringify(data));
		}
	};



})(jQuery, window, window.il.Chat);